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
        // Get filter parameters with better defaults
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
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
            'total_time' => $this->calculateTotalTime($query, $startDate, $endDate),
            'avg_completion_time' => $this->calculateAverageCompletionTime($query, $startDate, $endDate),
        ];

        // Daily completion trend
        $completionTrend = $this->getCompletionTrend($startDate, $endDate, $worker, $serviceTemplate, $status);

        // Worker performance data
        $workerPerformance = $this->getWorkerPerformance($startDate, $endDate, $worker, $serviceTemplate, $status);

        // Service type distribution
        $serviceDistribution = $this->getServiceDistribution($startDate, $endDate, $worker, $serviceTemplate, $status);

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

    private function calculateTotalTime($query, $startDate, $endDate)
    {
        $workOrderIds = $query->pluck('id');
    
        return WorkOrderTime::whereIn('work_order_id', $workOrderIds)
            ->whereNotNull('ended_at')
            ->whereBetween('started_at', [$startDate, $endDate]) // Apply date range filter
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, ended_at)'));
    }

    private function calculateAverageCompletionTime($query, $startDate, $endDate)
    {
        // Get work order IDs from the filtered query
        $workOrderIds = $query->pluck('id');

        if ($workOrderIds->isEmpty()) {
            return 0;
        }

        // Calculate average time from work_order_times
        return WorkOrderTime::whereIn('work_order_id', $workOrderIds)
            ->whereNotNull('ended_at')
            ->whereBetween('started_at', [$startDate, $endDate]) // Apply date range filter
            ->select(DB::raw('
                AVG(TIMESTAMPDIFF(MINUTE, 
                    started_at, 
                    ended_at
                )) as avg_time
            '))
            ->first()
            ->avg_time ?? 0;
    }

    private function getWorkerPerformance($startDate, $endDate, $workerFilter = null, $serviceTemplateFilter = null, $statusFilter = null)
    {
        try {
            // Use the worker filter if provided, otherwise get all workers
            $workersQuery = User::whereHas('role', fn($q) => $q->where('slug', 'worker'));
            
            if ($workerFilter) {
                $workersQuery->where('id', $workerFilter);
            }
            
            $workers = $workersQuery->get();
            
            foreach($workers as $worker) {
                // Base query for work orders
                $baseQuery = WorkOrder::where('assigned_to', $worker->id)
                    ->whereBetween('created_at', [$startDate, $endDate]);
                
                // Apply additional filters
                if ($serviceTemplateFilter) {
                    $baseQuery->where('service_template_id', $serviceTemplateFilter);
                }
                
                if ($statusFilter) {
                    $baseQuery->where('status', $statusFilter);
                }
                
                // Get work order IDs with all filters applied
                $workOrderIds = $baseQuery->pluck('id');
                
                // Count total orders
                $worker->total_orders = $workOrderIds->count();
                
                // Count completed orders with filters
                $worker->completed_orders = $baseQuery->where('status', 'completed')->count();
                
                // Calculate total time
                $totalTime = WorkOrderTime::whereIn('work_order_id', $workOrderIds)
                    ->where('user_id', $worker->id)
                    ->whereNotNull('ended_at')
                    ->whereBetween('started_at', [$startDate, $endDate])
                    ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, ended_at)'));
                
                $worker->total_time = $totalTime ?? 0;
            }

            return $workers;

        } catch (\Exception $e) {
            \Log::error('Error in getWorkerPerformance: ' . $e->getMessage());
            return collect([]);
        }
    }

    private function getServiceDistribution($startDate, $endDate, $workerFilter = null, $serviceTemplateFilter = null, $statusFilter = null)
    {
        try {
            // Build the query with all filters
            $query = WorkOrder::whereBetween('created_at', [$startDate, $endDate]);
            
            if ($workerFilter) {
                $query->where('assigned_to', $workerFilter);
            }
            
            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }
            
            // For service template filter, we need special handling since we're grouping by service_template_id
            if ($serviceTemplateFilter) {
                $services = WorkOrder::where('service_template_id', $serviceTemplateFilter)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($workerFilter, function($q) use ($workerFilter) {
                        $q->where('assigned_to', $workerFilter);
                    })
                    ->when($statusFilter, function($q) use ($statusFilter) {
                        $q->where('status', $statusFilter);
                    })
                    ->select('service_template_id')
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                    ->with('serviceTemplate:id,name')
                    ->groupBy('service_template_id')
                    ->get();
            } else {
                // Group by service_template_id if no specific template is filtered
                $services = $query->select('service_template_id')
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                    ->with('serviceTemplate:id,name')
                    ->groupBy('service_template_id')
                    ->get();
            }

            foreach($services as $service) {
                // Get work order IDs for this service with all filters applied
                $workOrderQuery = WorkOrder::where('service_template_id', $service->service_template_id)
                    ->whereBetween('created_at', [$startDate, $endDate]);
                
                if ($workerFilter) {
                    $workOrderQuery->where('assigned_to', $workerFilter);
                }
                
                if ($statusFilter) {
                    $workOrderQuery->where('status', $statusFilter);
                }
                
                $workOrderIds = $workOrderQuery->pluck('id');
                
                if ($workOrderIds->isEmpty()) {
                    $service->total_time = 0;
                    continue;
                }

                // Calculate times for these work orders
                $times = WorkOrderTime::whereIn('work_order_id', $workOrderIds)
                    ->whereNotNull('ended_at')
                    ->whereBetween('started_at', [$startDate, $endDate])
                    ->select(
                        DB::raw('SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as total_time')
                    )
                    ->first();
                
                $service->total_time = $times->total_time ?? 0;
            }

            return $services;
            
        } catch (\Exception $e) {
            \Log::error('Error in getServiceDistribution: ' . $e->getMessage());
            return collect([]);
        }
    }

    private function getCompletionTrend($startDate, $endDate, $workerFilter = null, $serviceTemplateFilter = null, $statusFilter = null)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        $completed = [];
        $created = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('M d');
            
            // Build query for completed work orders on this date
            $completedQuery = WorkOrder::whereDate('completed_at', $date)
                ->where('status', 'completed');
                
            // Apply additional filters
            if ($workerFilter) {
                $completedQuery->where('assigned_to', $workerFilter);
            }
            
            if ($serviceTemplateFilter) {
                $completedQuery->where('service_template_id', $serviceTemplateFilter);
            }
            
            $completed[] = $completedQuery->count();
            
            // Build query for created work orders on this date
            $createdQuery = WorkOrder::whereDate('created_at', $date);
            
            // Apply additional filters
            if ($workerFilter) {
                $createdQuery->where('assigned_to', $workerFilter);
            }
            
            if ($serviceTemplateFilter) {
                $createdQuery->where('service_template_id', $serviceTemplateFilter);
            }
            
            if ($statusFilter) {
                $createdQuery->where('status', $statusFilter);
            }
            
            $created[] = $createdQuery->count();
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