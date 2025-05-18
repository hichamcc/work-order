<?php

namespace App\Console\Commands;

use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StopCompletedWorkTimers extends Command
{
    protected $signature = 'workorders:stop-completed-timers';
    protected $description = 'Stop all running timers for completed work orders';

    public function handle()
    {
        $this->info('Checking for running timers on completed work orders...');
        
        // Find all running timers for completed work orders
        $runningTimers = WorkOrderTime::whereNull('ended_at')
            ->whereHas('workOrder', function ($query) {
                $query->where('status', 'completed')
                    ->orWhere('status', 'on_hold');
            })
            ->get();
            
        if ($runningTimers->isEmpty()) {
            $this->info('No running timers found for completed or on-hold work orders.');
            return 0;
        }
        
        $this->info("Found {$runningTimers->count()} running timers that need to be stopped.");
        
        $bar = $this->output->createProgressBar($runningTimers->count());
        $bar->start();
        
        DB::beginTransaction();
        try {
            foreach ($runningTimers as $timer) {
                $timer->update([
                    'ended_at' => $timer->workOrder->completed_at ?? $timer->workOrder->updated_at ?? now()
                ]);
                
                $bar->advance();
            }
            
            DB::commit();
            $bar->finish();
            
            $this->newLine();
            $this->info('Successfully stopped all running timers for completed and on-hold work orders.');
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}