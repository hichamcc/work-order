<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\ServiceTemplate;
use App\Models\WorkOrderTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->get('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->end_date) : Carbon::now();
        $worker = $request->get('worker');
        $serviceTemplate = $request->get('service_template');
        $status = $request->get('status');

        // Base query with filters
        $query = WorkOrder::query()
            ->when($worker, function($q) use ($worker) {
                $q->where('assigned_to', $worker);
            })
            ->when($serviceTemplate, function($q) use ($serviceTemplate) {
                $q->where('service_template_id', $serviceTemplate);
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Summary statistics
        $summary = [
            'total_orders' => $query->count(),
            'completed_orders' => (clone $query)->where('status', 'completed')->count(),
            'in_progress_orders' => (clone $query)->where('status', 'in_progress')->count(),
            'on_hold_orders' => (clone $query)->where('status', 'on_hold')->count(),
            'total_time' => $this->calculateTotalTime($query),
            'avg_completion_time' => $this->calculateAverageCompletionTime($query),
        ];

        // Daily completion trend
        $completionTrend = $this->getCompletionTrend($startDate, $endDate);

        // Worker performance data
        $workerPerformance = $this->getWorkerPerformance($startDate, $endDate);

        // Service type distribution
        $serviceDistribution = $this->getServiceDistribution($startDate, $endDate);

        // Status distribution
        $statusDistribution = $this->getStatusDistribution($query);

        // Get filter options
        $workers = User::whereHas('role', fn($q) => $q->where('slug', 'worker'))->get();
        $serviceTemplates = ServiceTemplate::all();
        $statuses = ['new', 'in_progress', 'on_hold', 'completed'];

        return view('admin.reports.index', compact(
            'summary',
            'completionTrend',
            'workerPerformance',
            'serviceDistribution',
            'statusDistribution',
            'workers',
            'serviceTemplates',
            'statuses',
            'startDate',
            'endDate',
            'worker',
            'serviceTemplate',
            'status'
        ));
    }

    private function calculateTotalTime($query)
    {
       $workOrderIds = $query->pluck('id');
    
       return WorkOrderTime::whereIn('work_order_id', $workOrderIds)
           ->whereNotNull('ended_at')
           ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, ended_at)'));
    }



    private function calculateAverageCompletionTime($query)
{
    // Get work order IDs from the filtered query
    $workOrderIds = $query->pluck('id');

    // Calculate average time from work_order_times
    return WorkOrderTime::whereIn('work_order_id', $workOrderIds)
        ->whereNotNull('ended_at')
        ->select(DB::raw('
            AVG(TIMESTAMPDIFF(MINUTE, 
                started_at, 
                ended_at
            )) as avg_time
        '))
        ->first()
        ->avg_time ?? 0;
}

//  the worker performance query
private function getWorkerPerformance($startDate, $endDate)
{
    try {
        $workers = User::whereHas('role', fn($q) => $q->where('slug', 'worker'))
            ->withCount(['assignedWorkOrders as total_orders' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['assignedWorkOrders as completed_orders' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate]);
            }])
            ->get();

        foreach($workers as $worker) {
            // Calculate total time
            $totalTime = WorkOrderTime::where('user_id', $worker->id)
                ->whereNotNull('ended_at')
                ->whereBetween('started_at', [$startDate, $endDate])
                ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, ended_at)'));
            
            // Calculate average time
            $avgTime = WorkOrderTime::where('user_id', $worker->id)
                ->whereNotNull('ended_at')
                ->whereBetween('started_at', [$startDate, $endDate])
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, ended_at)'));

            $worker->total_time = $totalTime ?? 0;
            $worker->average_time = $avgTime ?? 0;
        }

        return $workers;

    } catch (\Exception $e) {
        \Log::error('Error in getWorkerPerformance: ' . $e->getMessage());
        return collect([]);
    }
}

// the service distribution query
private function getServiceDistribution($startDate, $endDate)
{
    $services = WorkOrder::whereBetween('created_at', [$startDate, $endDate])
        ->select('service_template_id')
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
        ->with('serviceTemplate:id,name')
        ->groupBy('service_template_id')
        ->get();

    foreach($services as $service) {
        // Get times for this service
        $times = WorkOrderTime::whereHas('workOrder', function($q) use ($service) {
                $q->where('service_template_id', $service->service_template_id)
                  ->where('status', 'completed');
            })
            ->whereNotNull('ended_at')
            ->select(
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_time'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as total_time')
            )
            ->first();
        
        $service->avg_time = $times->avg_time ?? 0;
        $service->total_time = $times->total_time ?? 0;
    }

    return $services;
}




    private function getCompletionTrend($startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        $completed = [];
        $created = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('M d');
            
            $completed[] = WorkOrder::whereDate('completed_at', $date)
                ->where('status', 'completed')
                ->count();
                
            $created[] = WorkOrder::whereDate('created_at', $date)
                ->count();
        }

        return [
            'labels' => $dates,
            'completed' => $completed,
            'created' => $created
        ];
    }



    private function getStatusDistribution($query)
    {
        return $query->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }
}