<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trailer;
use App\Models\TrailerService;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrailerController extends Controller
{
    /**
     * Trailers, defaulting to the ones flagged as needing service.
     *
     * Mapon holds no trailer mileage, so unlike trucks there is nothing to
     * compute: a trailer is on this list because somebody put it there.
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'needs_service');

        $query = Trailer::with(['workOrder.assignedTo', 'flaggedBy'])->active();

        if ($filter === 'needs_service') {
            $query->needingService();
        }

        if ($request->filled('search')) {
            $search = $request->search;

            // Match either the plate or the text the workshop uses for it.
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        $trailers = $query
            ->orderByDesc('needs_service')
            ->orderBy('plate_number')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'needs_service' => Trailer::active()->needingService()->count(),
            'all' => Trailer::active()->count(),
        ];

        $workers = User::whereHas('role', function ($q) {
            $q->where('slug', 'worker');
        })->orderBy('name')->get();

        return view('admin.trailers.index', compact('trailers', 'filter', 'counts', 'workers'));
    }

    /**
     * Flag a trailer for service, or clear the flag.
     */
    public function flag(Request $request, Trailer $trailer)
    {
        $validated = $request->validate([
            'service_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $trailer->update([
            'needs_service' => true,
            'service_note' => $validated['service_note'] ?? null,
            'flagged_at' => now(),
            'flagged_by' => auth()->id(),
        ]);

        return back()->with('success', "{$trailer->display_name} marked as needing service.");
    }

    /**
     * Raise a work order for a flagged trailer.
     */
    public function assign(Request $request, Trailer $trailer)
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($trailer->work_order_id) {
            return back()->with('error', 'This trailer already has a work order.');
        }

        try {
            DB::beginTransaction();

            $workOrder = WorkOrder::create([
                'title' => 'Trailer service - '.$trailer->display_name,
                'description' => trim("Service for trailer {$trailer->display_name}.\n".($trailer->service_note ?? '')),
                'assigned_to' => $validated['assigned_to'],
                'created_by' => auth()->id(),
                'status' => 'new',
                'priority' => 'medium',
                'due_date' => $validated['due_date'] ?? null,
                'asset_type' => 'trailer',
            ]);

            $trailer->update(['work_order_id' => $workOrder->id]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Could not create the work order: '.$e->getMessage());
        }

        return back()->with('success', "Work order #{$workOrder->id} created for {$trailer->display_name}.");
    }

    /**
     * Record the service and take the trailer off the list.
     */
    public function complete(Request $request, Trailer $trailer)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = auth()->user();
        $isAssignedWorker = $trailer->work_order_id
            && $trailer->workOrder
            && $trailer->workOrder->assigned_to === $user->id;

        if (! $user->isAdmin() && ! $isAssignedWorker) {
            abort(403, 'You cannot complete this service.');
        }

        try {
            DB::beginTransaction();

            TrailerService::create([
                'trailer_id' => $trailer->id,
                'work_order_id' => $trailer->work_order_id,
                'recorded_by' => $user->id,
                'note' => $validated['note'] ?? $trailer->service_note,
                'serviced_at' => now(),
            ]);

            // Cleared for the next cycle: the flag, its note and the linked job.
            $trailer->update([
                'needs_service' => false,
                'service_note' => null,
                'flagged_at' => null,
                'flagged_by' => null,
                'work_order_id' => null,
                'last_serviced_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Could not record the service: '.$e->getMessage());
        }

        return back()->with('success', "{$trailer->display_name} marked as serviced.");
    }

    /**
     * Service history, optionally narrowed to one trailer.
     */
    public function history(Request $request)
    {
        $query = TrailerService::with(['trailer', 'workOrder', 'recordedBy']);

        if ($request->filled('trailer_id')) {
            $query->where('trailer_id', $request->trailer_id);
        }

        $services = $query->orderByDesc('serviced_at')->paginate(25)->withQueryString();
        $trailers = Trailer::active()->orderBy('plate_number')->get(['id', 'plate_number', 'label']);

        return view('admin.trailers.history', compact('services', 'trailers'));
    }
}
