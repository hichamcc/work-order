<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use App\Models\WorkOrderPart;
use App\Models\StockAdjustment;
use App\Models\ServiceTemplate;
use App\Models\User;

use App\Models\PartInstance;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['serviceTemplate', 'checklistItems'])
            ->where(function ($q) {
                $q->where('assigned_to', auth()->id())
                ->orWhereHas('helpers', function ($helperQuery) {
                    $helperQuery->where('users.id', auth()->id());
                });
            });
    
        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['new', 'in_progress', 'on_hold', 'completed']);
        }
    
        // Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
    
        // Date Range Filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                    break;
            }
        }
    
        $workOrders = $query->withCount([
                'checklistItems', 
                'checklistItems as completed_items_count' => function ($query) {
                    $query->where('is_completed', true);
                }
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();
    
        // Get active timings
        $activeTimings = WorkOrderTime::whereIn('work_order_id', $workOrders->pluck('id'))
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->pluck('work_order_id');
    
        // Get lists for filters
        $statuses = ['new', 'in_progress', 'on_hold', 'completed'];
        $priorities = ['low', 'medium', 'high'];
        $dateRanges = [
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month'
        ];
    
        return view('worker.work-orders.index', compact(
            'workOrders', 
            'activeTimings',
            'statuses',
            'priorities',
            'dateRanges'
        ));
    }

    public function show(WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }

        $workOrder->load([
            'serviceTemplate',
            'checklistItems.checklistItem',
            'checklistItems.completedByUser',
            'checklistItems.photos',
            'parts.part',
            'times',
            'comments.user',
        ]);

        $activeTiming = $workOrder->times()
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        $parts = Part::where('is_active', true)->get();

        return view('worker.work-orders.show', compact('workOrder', 'activeTiming', 'parts'));
    }

    public function startWork(WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }


        if ($workOrder->status === 'new') {
            $workOrder->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        WorkOrderTime::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
        ]);

        return back()->with('success', 'Work started successfully.');
    }

    public function pauseWork(WorkOrder $workOrder)
    {
      
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }

        $activeTiming = $workOrder->times()
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        if ($activeTiming) {
            $activeTiming->update([
                'ended_at' => now(),
            ]);
        }

        return back()->with('success', 'Work paused successfully.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }


        $validated = $request->validate([
            'status' => 'required|in:in_progress,on_hold,completed',
            'hold_reason' => 'required_if:status,on_hold',
        ]);

        // Check if all required checklist items are completed when marking as completed
        if ($validated['status'] === 'completed') {
            $incompleteRequired = $workOrder->checklistItems()
                ->whereHas('checklistItem', function ($query) {
                    $query->where('is_required', true);
                })
                ->where('is_completed', false)
                ->exists();

            if ($incompleteRequired) {
                return back()->with('error', 'Please complete all required checklist items before marking the work order as completed.');
            }
        }

        $workOrder->update([
            'status' => $validated['status'],
            'hold_reason' => $validated['status'] === 'on_hold' ? $validated['hold_reason'] : null,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Work order status updated successfully.');
    }

    public function updateChecklistItem(Request $request, WorkOrder $workOrder, $checklistItemId)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }

    
        $validated = $request->validate([
            'is_completed' => 'required|boolean',
            'notes' => 'nullable|string',
            'photos.*' => 'nullable|image|max:5120', // 5MB max
        ]);
    
        try {
            DB::beginTransaction();
    
            // First, find the WorkOrderChecklistItem (not ChecklistItem)
            $workOrderChecklistItem = $workOrder->checklistItems()->findOrFail($checklistItemId);
            
            $workOrderChecklistItem->update([
                'is_completed' => $validated['is_completed'],
                'completed_at' => $validated['is_completed'] ? now() : null,
                'completed_by' => $validated['is_completed'] ? auth()->id() : null,
                'notes' => $validated['notes'],
            ]);
    
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('work-orders/' . $workOrder->id, 'public');
                    
                    $workOrder->photos()->create([
                        'work_order_id' => $workOrder->id,
                        'checklist_item_id' => $workOrderChecklistItem->id, // Changed to match model
                        'file_path' => $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'uploaded_by' => auth()->id(),
                        'description' => $request->input('description', null), // Optional description
                    ]);
                }
            }
    
            DB::commit();
            return back()->with('success', 'Checklist item updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating checklist item: ' . $e->getMessage());
        }
    }

    public function addPart(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
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
                        'notes' => "Serial #{$instance->serial_number} used in Work Order #{$workOrder->id}",
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
                $note = "Used in Work Order #{$workOrder->id} - {$workOrder->title}";
    
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

    public function addComment(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id() && !$workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }


        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $workOrder->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }




    public function create()
{
    // For worker-created orders, we only need templates
    $templates = ServiceTemplate::where('is_active', true)->get();
    
    // No need to pass workers since they can only assign to themselves
    // Optionally, you could load helpers they can select
    $potentialHelpers = User::whereHas('role', function($q) {
        $q->where('slug', 'worker');
    })
    ->where('id', '!=', auth()->id()) // Exclude themselves
    ->get();

    return view('worker.work-orders.create', compact('templates', 'potentialHelpers'));
}

public function store(Request $request)
{
    try {
        // Custom validation for worker creation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_template_id' => 'nullable|exists:service_templates,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'helpers' => 'nullable|array',
            'helpers.*' => 'exists:users,id',
        ]);
        
        DB::beginTransaction();
        
        // Extract helpers from the validated data if they exist
        $helpers = $validated['helpers'] ?? [];
        unset($validated['helpers']);
        
        // Create work order with worker as the assigned_to
        $workOrder = WorkOrder::create([
            ...$validated,
            'assigned_to' => auth()->id(), // Auto-assign to themselves
            'created_by' => auth()->id(),
            'status' => 'new', // Or you could set it to 'in_progress' directly
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
            $workOrder->helpers()->sync($helpers);
        }

        DB::commit();

        return redirect()
            ->route('worker.work-orders.index')
            ->with('success', 'Work order created successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->withInput()
            ->with('error', 'Error creating work order: ' . $e->getMessage());
    }
}
}