<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            // Total stats
            'totalWorkOrders' => WorkOrder::count(),
            'pendingWorkOrders' => WorkOrder::whereIn('status', ['new', 'in_progress'])->count(),
            'activeWorkers' => User::where('role_id', 2)->where('is_active', true)->count(),
            'completedToday' => WorkOrder::whereDate('completed_at', Carbon::today())
                                       ->where('status', 'completed')
                                       ->count(),

            // Recent work orders
            'recentWorkOrders' => WorkOrder::with(['assignedTo'])
                                         ->latest()
                                         ->take(5)
                                         ->get(),
        ];

        return view('admin.dashboard', $data);
    }
}