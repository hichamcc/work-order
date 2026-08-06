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

        // Trucks with no recorded oil service have no baseline to measure from,
        // so only the ones we have serviced before are checked.
        $lastServices = TruckOilService::latestPerTruck();

        if ($lastServices->isEmpty()) {
            $this->info('No oil services recorded yet; nothing to check.');

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
            foreach ($lastServices as $truckNumber => $lastService) {
                $unit = $units[$mapon->normalisePlate($truckNumber)] ?? null;

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

                $driven = $reading['km'] - $lastService->km;

                if ($driven < $interval) {
                    continue;
                }

                $existing = TruckServiceAlert::open()
                    ->where('truck_number', $truckNumber)
                    ->first();

                $note = sprintf(
                    'Driven %s km since the last oil service at %s km. Oil service is due.',
                    number_format($driven),
                    number_format($lastService->km)
                );

                $attributes = [
                    'current_km' => $reading['km'],
                    'last_service_km' => $lastService->km,
                    'km_since_service' => $driven,
                    'km_source' => $reading['source'],
                    'note' => $note,
                    'flagged_at' => now(),
                ];

                // Refresh the open alert rather than stacking a new row each night.
                if ($existing) {
                    $existing->update($attributes);
                } else {
                    TruckServiceAlert::create([
                        'truck_number' => $truckNumber,
                        ...$attributes,
                    ]);
                }

                $this->line("  {$truckNumber}: due ({$driven} km since service)");
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
