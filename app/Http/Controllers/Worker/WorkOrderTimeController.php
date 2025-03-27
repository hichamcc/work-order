<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;

use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use Illuminate\Http\Request;

class WorkOrderTimeController extends Controller
{
    public function startWork(WorkOrder $workOrder)
    {
        // Check if work order is assigned to current user
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }


        // Check if there's already an active timing
        $activeTiming = $workOrder->times()
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->exists();

        if ($activeTiming) {
            return back()->with('error', 'You already have an active timing for this work order.');
        }

        // Update work order status if it's new
        if ($workOrder->status === 'new') {
            $workOrder->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        // Create new timing record
        WorkOrderTime::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
        ]);

        return back()->with('success', 'Work timer started successfully.');
    }

    public function pauseWork(WorkOrder $workOrder)
    {
        // Check if work order is assigned to current user
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }


        // Find and end active timing
        $activeTiming = $workOrder->times()
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        if (!$activeTiming) {
            return back()->with('error', 'No active timing found.');
        }

        $activeTiming->update([
            'ended_at' => now(),
        ]);

        return back()->with('success', 'Work timer paused successfully.');
    }

    public function timeTrackingView(WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }


        $activeTiming = $workOrder->times()
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        return view('worker.work-orders.time-tracking', compact('workOrder', 'activeTiming'));
    }
}