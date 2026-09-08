<?php

namespace App\Console\Commands;

use App\Models\TruckOilService;
use App\Models\TruckServiceAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanPrematureOilServices extends Command
{
    protected $signature = 'trucks:clean-premature-oil-services {--force : Delete them. Without it the command only reports}';

    protected $description = 'Remove oil services recorded against work orders that were never completed';

    /**
     * Oil services used to be logged when a work order was created rather than
     * when it was finished, so some trucks show a service for work that has not
     * been done. Those records are removed and the trucks put back on the list.
     */
    public function handle()
    {
        $premature = TruckOilService::with('workOrder')
            ->whereHas('workOrder', function ($query) {
                $query->where('status', '!=', 'completed');
            })
            ->get();

        if ($premature->isEmpty()) {
            $this->info('No oil services are attached to unfinished work orders.');

            return 0;
        }

        $this->warn($premature->count().' oil service(s) recorded against work orders that are not completed:');

        $this->table(
            ['Service', 'Truck', 'KM', 'Work order', 'Status'],
            $premature->map(fn ($service) => [
                $service->id,
                $service->truck_number,
                number_format($service->km),
                '#'.$service->work_order_id,
                $service->workOrder->status ?? '—',
            ])->all()
        );

        if (! $this->option('force')) {
            $this->newLine();
            $this->warn('Dry run: nothing was deleted. Re-run with --force to remove them.');

            return 0;
        }

        DB::beginTransaction();

        try {
            foreach ($premature as $service) {
                // Drop the follow-on alert this service created and reopen the
                // one it closed, so the truck is tracked as unserviced again.
                TruckServiceAlert::where('truck_number', $service->truck_number)
                    ->where('source', 'service_history')
                    ->where('last_service_km', $service->km)
                    ->delete();

                TruckServiceAlert::where('truck_number', $service->truck_number)
                    ->whereNotNull('resolved_at')
                    ->latest('resolved_at')
                    ->limit(1)
                    ->update(['resolved_at' => null]);

                $service->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Cleanup failed: '.$e->getMessage());

            return 1;
        }

        $this->info('Removed '.$premature->count().' premature oil service record(s).');

        return 0;
    }
}
