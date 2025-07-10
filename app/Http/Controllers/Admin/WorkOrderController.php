<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use App\Models\User;
use App\Models\Customer;
use App\Models\ServiceTemplate;
use App\Models\Part;
use App\Models\PartInstance;
use App\Models\WorkOrderPart;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\StoreWorkOrderRequest;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['assignedTo', 'serviceTemplate', 'checklistItems','customer'])
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
            // Customer filter
            if ($request->filled('customer')) {
                $query->where('customer_id', $request->customer);
            }


        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $workOrders = $query->paginate(10)->withQueryString();
        $workers = User::whereHas('role', function($q) {
            $q->where('slug', 'worker');
        })->get();

        $customers = Customer::orderBy('is_default', 'desc')
        ->orderBy('name')
        ->get();

        return view('admin.work-orders.index', compact('workOrders', 'workers' , 'customers'));
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
    
            // Get validated data
            $validated = $request->validated();
            
            // Extract helpers from the validated data if they exist
            $helpers = $validated['helpers'] ?? [];
            unset($validated['helpers']); // Remove helpers from validated data before creating WorkOrder
    
            $workOrder = WorkOrder::create([
                ...$validated,
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
            
            // Sync helpers (if any)
            if (!empty($helpers)) {
                // Filter out the primary worker from helpers to avoid duplication
                $helpers = array_filter($helpers, function($helperId) use ($workOrder) {
                    return $helperId != $workOrder->assigned_to;
                });
                
                if (!empty($helpers)) {
                    $workOrder->helpers()->sync($helpers);
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
    // Load the workOrder with its relationships, including helpers
    $workOrder->load(['assignedTo', 'serviceTemplate', 'checklistItems.checklistItem', 'helpers']);
    
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
        'customer_id'=>'nullable',
        'description' => 'nullable|string',
        'assigned_to' => 'required|exists:users,id',
        'priority' => 'required|in:low,medium,high,urgent',
        'due_date' => 'nullable|date',
        'helpers' => 'nullable|array',
        'helpers.*' => 'exists:users,id',
    ]);

    // Extract helpers before updating the work order
    $helpers = $validated['helpers'] ?? [];
    unset($validated['helpers']);
    
    // Update the work order
    $workOrder->update($validated);
    
    // Sync helpers, filtering out the primary worker
    if (isset($helpers)) {
        $helpers = array_filter($helpers, function($helperId) use ($workOrder) {
            return $helperId != $workOrder->assigned_to;
        });
        
        $workOrder->helpers()->sync($helpers);
    } else {
        // If no helpers were selected, remove all existing helpers
        $workOrder->helpers()->sync([]);
    }

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

    public function toggleInvoice(WorkOrder $workOrder)
{
    $workOrder->update([
        'invoiced' => !$workOrder->invoiced
    ]);
    
    return back()->with('success', 'Invoice status updated successfully.');
}

    public function editTimes(WorkOrder $workOrder)
    {
        $workOrder->load(['times.user', 'assignedTo']);
        
        return view('admin.work-orders.edit-times', compact('workOrder'));
    }

    public function updateTime(Request $request, WorkOrder $workOrder, WorkOrderTime $workOrderTime)
    {
        $validated = $request->validate([
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'notes' => 'nullable|string|max:1000',
        ]);

        $workOrderTime->update($validated);

        return back()->with('success', 'Time entry updated successfully.');
    }

    public function destroyTime(WorkOrder $workOrder, WorkOrderTime $workOrderTime)
    {
        $workOrderTime->delete();

        return back()->with('success', 'Time entry deleted successfully.');
    }

    public function addPart(Request $request, WorkOrder $workOrder)
    {
        // Only allow adding parts to completed work orders
        if ($workOrder->status !== 'completed') {
            return back()->with('error', 'Parts can only be added to completed work orders.');
        }

        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'serial_numbers' => 'nullable|array',
            'serial_numbers.*' => 'exists:part_instances,id',
        ]);
    
        try {
            DB::beginTransaction();
    
            $part = Part::findOrFail($validated['part_id']);
    
            // Handle serialized parts
            if ($part->track_serials) {
                // Validate that serial numbers were provided
                if (empty($validated['serial_numbers']) || count($validated['serial_numbers']) != $validated['quantity']) {
                    return back()->with('error', 'Please select ' . $validated['quantity'] . ' serial numbers.');
                }
    
                // Check if all the serial numbers are available
                $partInstances = PartInstance::whereIn('id', $validated['serial_numbers'])
                    ->where('part_id', $part->id)
                    ->where('status', 'in_stock')
                    ->get();
    
                if ($partInstances->count() != count($validated['serial_numbers'])) {
                    return back()->with('error', 'Some selected serial numbers are not available.');
                }
    
                // Create work order part record
                $workOrderPart = $workOrder->parts()->create([
                    'part_id' => $validated['part_id'],
                    'quantity' => $validated['quantity'],
                    'cost_at_time' => $part->cost,
                ]);
    
                // Update part instances status and link to work order
                foreach ($partInstances as $instance) {
                    $instance->update([
                        'status' => 'assigned',
                        'work_order_id' => $workOrder->id
                    ]);
    
                    // Create stock adjustment for each serial
                    $part->stockAdjustments()->create([
                        'adjusted_by' => auth()->id(),
                        'previous_stock' => $part->stock,
                        'new_stock' => $part->stock - 1,
                        'adjustment_quantity' => -1,
                        'adjustment_type' => 'remove',
                        'notes' => "Serial #{$instance->serial_number} added to Work Order #{$workOrder->id} by admin",
                        'part_instance_id' => $instance->id
                    ]);
    
                    // Update part stock
                    $part->decrement('stock');
                }
            } else {
                // Handle regular non-serialized parts
                // Check if enough stock is available
                if ($part->stock < $validated['quantity']) {
                    return back()->with('error', 'Not enough stock available. Current stock: ' . $part->stock);
                }
    
                // Create work order part record
                $workOrder->parts()->create([
                    'part_id' => $validated['part_id'],
                    'quantity' => $validated['quantity'],
                    'cost_at_time' => $part->cost,
                ]);
    
                // Generate automatic note
                $note = "Added to Work Order #{$workOrder->id} - {$workOrder->title} by admin";
    
                // Create stock adjustment record
                $part->stockAdjustments()->create([
                    'adjusted_by' => auth()->id(),
                    'previous_stock' => $part->stock,
                    'new_stock' => $part->stock - $validated['quantity'],
                    'adjustment_quantity' => -$validated['quantity'],
                    'adjustment_type' => 'remove',
                    'notes' => $note,
                ]);
    
                // Update part stock
                $part->update([
                    'stock' => $part->stock - $validated['quantity']
                ]);
            }
    
            DB::commit();
            return back()->with('success', 'Part added successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adding part: ' . $e->getMessage());
        }
    }
}