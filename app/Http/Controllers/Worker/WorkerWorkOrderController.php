<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderTime;
use App\Models\WorkOrderPart;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerWorkOrderController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with(['serviceTemplate', 'checklistItems'])
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['new', 'in_progress', 'on_hold'])
            ->withCount(['checklistItems', 'checklistItems as completed_items_count' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->latest()
            ->paginate(10);

        return view('worker.work-orders.index', compact('workOrders'));
    }

    public function show(WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id()) {
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
        if ($workOrder->assigned_to !== auth()->id()) {
            abort(403);
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
        if ($workOrder->assigned_to !== auth()->id()) {
            abort(403);
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
        if ($workOrder->assigned_to !== auth()->id()) {
            abort(403);
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
        if ($workOrder->assigned_to !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'is_completed' => 'required|boolean',
            'notes' => 'nullable|string',
            'photos.*' => 'nullable|image|max:5120', // 5MB max
        ]);

        try {
            DB::beginTransaction();

            $checklistItem = $workOrder->checklistItems()->findOrFail($checklistItemId);
            
            $checklistItem->update([
                'is_completed' => $validated['is_completed'],
                'completed_at' => $validated['is_completed'] ? now() : null,
                'completed_by' => $validated['is_completed'] ? auth()->id() : null,
                'notes' => $validated['notes'],
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('work-orders/' . $workOrder->id, 'public');
                    
                    $workOrder->photos()->create([
                        'checklist_item_id' => $checklistItem->id,
                        'file_path' => $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'uploaded_by' => auth()->id(),
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
        if ($workOrder->assigned_to !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $part = Part::findOrFail($validated['part_id']);

        $workOrder->parts()->create([
            'part_id' => $validated['part_id'],
            'quantity' => $validated['quantity'],
            'cost_at_time' => $part->cost,
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Part added successfully.');
    }

    public function addComment(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->assigned_to !== auth()->id()) {
            abort(403);
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
}