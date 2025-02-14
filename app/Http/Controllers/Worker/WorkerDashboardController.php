<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkerDashboardController extends Controller
{
    public function index()
    {
        // Get active time entry if any exists
        $activeTime = WorkOrderTime::with('workOrder')
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        // Get assigned work orders that are not completed
        $activeOrders = WorkOrder::with(['serviceTemplate', 'checklistItems'])
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['new', 'in_progress', 'on_hold'])
            ->withCount([
                'checklistItems',
                'checklistItems as completed_items_count' => function ($query) {
                    $query->where('is_completed', true);
                }
            ])
            ->latest()
            ->take(5)
            ->get();

        // Calculate statistics
        $stats = [
            'active_orders' => WorkOrder::where('assigned_to', auth()->id())
                ->whereIn('status', ['new', 'in_progress'])
                ->count(),

            'on_hold' => WorkOrder::where('assigned_to', auth()->id())
                ->where('status', 'on_hold')
                ->count(),

            'completed_today' => WorkOrder::where('assigned_to', auth()->id())
                ->where('status', 'completed')
                ->whereDate('completed_at', Carbon::today())
                ->count(),

            'hours_today' => $this->calculateHoursToday(),

            'total_completed' => WorkOrder::where('assigned_to', auth()->id())
                ->where('status', 'completed')
                ->count(),

            'urgent_orders' => WorkOrder::where('assigned_to', auth()->id())
                ->whereIn('status', ['new', 'in_progress'])
                ->where('priority', 'urgent')
                ->count()
        ];

        return view('worker.dashboard', compact('activeTime', 'activeOrders', 'stats'));
    }

    private function calculateHoursToday()
    {
        $times = WorkOrderTime::where('user_id', auth()->id())
            ->whereDate('started_at', Carbon::today())
            ->get();

        $totalMinutes = 0;

        foreach ($times as $time) {
            $endTime = $time->ended_at ?? now();
            $totalMinutes += $time->started_at->diffInMinutes($endTime);
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf("%d:%02d", $hours, $minutes);
    }
}