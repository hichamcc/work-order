<?php

namespace App\Console\Commands;

use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlignWorkOrderTimers extends Command
{
    protected $signature = 'workorders:align-timers';
    protected $description = 'Ensure all timer end times match or precede work order completion times';

    public function handle()
    {
        $this->info('Checking for timers that need alignment with work order completion times...');
        
        // Get completed work orders
        $completedWorkOrders = WorkOrder::whereNotNull('completed_at')
            ->orWhere('status', 'completed')
            ->get();
            
        if ($completedWorkOrders->isEmpty()) {
            $this->info('No completed work orders found.');
            return 0;
        }
        
        $this->info("Found {$completedWorkOrders->count()} completed work orders to check.");
        
        $fixedTimers = 0;
        $processedWorkOrders = 0;
        
        $bar = $this->output->createProgressBar($completedWorkOrders->count());
        $bar->start();
        
        DB::beginTransaction();
        try {
            foreach ($completedWorkOrders as $workOrder) {
                $completionTime = $workOrder->completed_at ?? $workOrder->updated_at;
                
                // Fix timers with no end time
                $nullEndTimers = $workOrder->times()
                    ->whereNull('ended_at')
                    ->update(['ended_at' => $completionTime]);
                    
                $fixedTimers += $nullEndTimers;
                
                // Fix timers that end after completion
                $lateEndTimers = $workOrder->times()
                    ->whereNotNull('ended_at')
                    ->where('ended_at', '>', $completionTime)
                    ->update(['ended_at' => $completionTime]);
                    
                $fixedTimers += $lateEndTimers;
                
                $processedWorkOrders++;
                $bar->advance();
            }
            
            DB::commit();
            $bar->finish();
            
            $this->newLine(2);
            $this->info("Processed {$processedWorkOrders} completed work orders.");
            $this->info("Fixed {$fixedTimers} timers to align with work order completion times.");
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}