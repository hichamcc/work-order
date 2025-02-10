<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\ServiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\StoreWorkOrderRequest;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['assignedTo', 'serviceTemplate', 'checklistItems'])
            ->withCount(['checklistItems', 'checklistItems as completed_items_count' => function ($query) {
                $query->where('is_completed', true);
            }]);

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned worker
        if ($request->filled('worker')) {
            $query->where('assigned_to', $request->worker);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $workOrders = $query->paginate(10)->withQueryString();
        $workers = User::whereHas('role', function($q) {
            $q->where('slug', 'worker');
        })->get();

        return view('admin.work-orders.index', compact('workOrders', 'workers'));
    }

    public function create()
    {
        $workers = User::whereHas('role', function($q) {
            $q->where('slug', 'worker');
        })->get();
        $templates = ServiceTemplate::where('is_active', true)->get();

        return view('admin.work-orders.create', compact('workers', 'templates'));
    }

    public function store(StoreWorkOrderRequest $request)
    {
        try {
            DB::beginTransaction();

            $workOrder = WorkOrder::create([
                ...$request->validated(),
                'created_by' => auth()->id(),
                'status' => 'new',
            ]);

            // If a service template is selected, copy its checklist items
            if ($request->filled('service_template_id')) {
                $template = ServiceTemplate::with('checklistItems')->find($request->service_template_id);
                foreach ($template->checklistItems as $item) {
                    $workOrder->checklistItems()->create([
                        'checklist_item_id' => $item->id,
                        'is_completed' => false,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.work-orders.index')
                ->with('success', 'Work order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error creating work order: ' . $e->getMessage());
        }
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'assignedTo',
            'createdBy',
            'serviceTemplate',
            'checklistItems.checklistItem',
            'checklistItems.completedByUser',
            'checklistItems.photos',
            'parts.part',
            'times',
            'comments.user',
        ]);

        return view('admin.work-orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load(['assignedTo', 'serviceTemplate', 'checklistItems.checklistItem']);
        $workers = User::whereHas('role', function($q) {
            $q->where('slug', 'worker');
        })->get();
        $templates = ServiceTemplate::where('is_active', true)->get();

        return view('admin.work-orders.edit', compact('workOrder', 'workers', 'templates'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $workOrder->update($validated);

        return redirect()
            ->route('admin.work-orders.index')
            ->with('success', 'Work order updated successfully.');
    }

    public function destroy(WorkOrder $workOrder)
    {
        if ($workOrder->status !== 'new') {
            return back()->with('error', 'Cannot delete work order that is in progress or completed.');
        }

        $workOrder->delete();

        return back()->with('success', 'Work order deleted successfully.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,on_hold,completed',
            'hold_reason' => 'required_if:status,on_hold',
        ]);

        $workOrder->update([
            'status' => $validated['status'],
            'hold_reason' => $validated['status'] === 'on_hold' ? $validated['hold_reason'] : null,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Work order status updated successfully.');
    }
}