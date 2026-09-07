<?php

namespace App\Console\Commands;

use App\Models\TruckOilService;
use App\Models\TruckServiceAlert;
use App\Services\MaponService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTruckOilServices extends Command
{
    protected $signature = 'trucks:check-oil-services';

    protected $description = 'Compare current Mapon mileage against the last oil service and flag trucks that are due';

    public function handle(MaponService $mapon)
    {
        $interval = (int) config('services.mapon.oil_service_interval_km', 120000);

        $this->info("Checking trucks against a {$interval} km oil service interval...");

        // A truck's baseline comes either from an oil service recorded here or,
        // until it has had one, from an imported reminder. A due point the admin
        // has corrected by hand counts the same way.
        $lastServices = TruckOilService::latestPerTruck();
        $imported = TruckServiceAlert::open()
            ->whereIn('source', ['mapon_reminder', 'manual'])
            ->whereNotNull('due_at_km')
            ->get()
            ->keyBy('truck_number');

        if ($lastServices->isEmpty() && $imported->isEmpty()) {
            $this->info('No oil services recorded and no reminders imported; nothing to check.');

            return 0;
        }

        $units = $mapon->units(fresh: true);

        if (empty($units)) {
            $this->error('Mapon returned no units. Check the API key.');

            return 1;
        }

        $flagged = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            // A truck serviced through the system supersedes its imported
            // reminder, so history is checked first and imports fill the rest.
            $truckNumbers = $lastServices->keys()
                ->merge($imported->keys())
                ->unique();

            foreach ($truckNumbers as $truckNumber) {
                $unit = $mapon->resolvePlate($truckNumber)['unit'];

                if (! $unit) {
                    $this->warn("  {$truckNumber}: not found in Mapon, skipped.");
                    $skipped++;
                    continue;
                }

                $reading = $mapon->readingFor($unit);

                if ($reading['km'] === null) {
                    $this->warn("  {$truckNumber}: no mileage available, skipped.");
                    $skipped++;
                    continue;
                }

                $lastService = $lastServices[$truckNumber] ?? null;

                if ($lastService) {
                    $driven = $reading['km'] - $lastService->km;

                    if ($driven < $interval) {
                        continue;
                    }

                    $attributes = [
                        'current_km' => $reading['km'],
                        'last_service_km' => $lastService->km,
                        'due_at_km' => $lastService->km + $interval,
                        'km_since_service' => $driven,
                        'km_source' => $reading['source'],
                        'source' => 'service_history',
                        'flagged_at' => now(),
                    ];

                    $summary = "due ({$driven} km since service)";
                } else {
                    // Imported reminder, or a due point the admin has corrected.
                    $alert = $imported[$truckNumber];
                    $dueAtKm = (int) $alert->due_at_km;

                    if ($reading['km'] < $dueAtKm) {
                        continue;
                    }

                    $over = $reading['km'] - $dueAtKm;

                    // The note belongs to whoever wrote it: the workshop's own
                    // text from the import, or the admin's correction. The due
                    // figures are columns on the list, so they are not repeated.
                    $attributes = [
                        'current_km' => $reading['km'],
                        'due_at_km' => $dueAtKm,
                        'km_source' => $reading['source'],
                        'source' => $alert->source,
                        'flagged_at' => now(),
                    ];

                    $summary = "due ({$over} km past the {$dueAtKm} km point)";
                }

                // Refresh the open alert rather than stacking a new row each night.
                TruckServiceAlert::updateOrCreate(
                    ['truck_number' => $truckNumber, 'resolved_at' => null],
                    $attributes
                );

                $this->line("  {$truckNumber}: {$summary}");
                $flagged++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: '.$e->getMessage());

            return 1;
        }

        $this->info("Done. {$flagged} truck(s) need service, {$skipped} skipped.");

        return 0;
    }
}
