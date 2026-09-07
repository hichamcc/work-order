<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TruckOilService;
use App\Models\TruckServiceAlert;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\MaponService;
use App\Services\OilServiceRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TruckServiceController extends Controller
{
    /**
     * Trucks the nightly check has flagged as needing an oil service.
     */
    public function index(Request $request)
    {
        $query = TruckServiceAlert::with(['workOrder.assignedTo']);

        // Default to the trucks that actually need booking in; the rest are
        // tracked but only shown when asked for.
        $filter = $request->input('filter', 'attention');

        if ($request->boolean('show_resolved')) {
            $query->resolved();
        } else {
            $query->open();

            match ($filter) {
                'overdue' => $query->overdue(),
                'all' => null,
                default => $query->needsAttention(),
            };
        }

        if ($request->filled('search')) {
            $query->where('truck_number', 'like', "%{$request->search}%");
        }

        // Most urgent first: the furthest past its service point at the top.
        // Imported rows measure that as current - due_at, history rows as the
        // distance driven beyond the interval.
        // Cast to signed: the columns are unsigned, so a truck that is not yet
        // due would underflow instead of sorting below the overdue ones.
        $alerts = $query->orderByRaw('COALESCE(CAST(current_km AS SIGNED) - CAST(due_at_km AS SIGNED), km_since_service, 0) DESC')
            ->paginate(25)
            ->withQueryString();

        $intervalKm = (int) config('services.mapon.oil_service_interval_km', 120000);

        $counts = [
            'overdue' => TruckServiceAlert::open()->overdue()->count(),
            'attention' => TruckServiceAlert::open()->needsAttention()->count(),
            'all' => TruckServiceAlert::open()->count(),
        ];

        $workers = User::whereHas('role', function ($q) {
            $q->where('slug', 'worker');
        })->orderBy('name')->get();

        return view('admin.trucks.service-due', compact('alerts', 'intervalKm', 'filter', 'counts', 'workers'));
    }

    /**
     * Book a flagged truck in: raise a work order for it and link the alert so
     * the list shows it is already being dealt with.
     */
    public function assign(Request $request, TruckServiceAlert $alert, MaponService $mapon)
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($alert->work_order_id) {
            return back()->with('error', 'This truck already has a work order.');
        }

        // Book against the reading as it stands now, not the one from the import.
        $reading = $mapon->readingForPlate($alert->truck_number);
        $km = $reading['km'] ?? $alert->current_km;

        try {
            DB::beginTransaction();

            $workOrder = WorkOrder::create([
                'title' => 'Oil service - '.$alert->truck_number,
                'description' => sprintf(
                    'Oil service for truck %s. Due at %s km, current reading %s km.',
                    $alert->truck_number,
                    number_format((int) $alert->due_at_km),
                    number_format((int) $km)
                ),
                'assigned_to' => $validated['assigned_to'],
                'created_by' => auth()->id(),
                'status' => 'new',
                'priority' => $alert->service_state === 'overdue' ? 'high' : 'medium',
                'due_date' => $validated['due_date'] ?? null,
                'asset_type' => 'truck',
                'truck_number' => $alert->truck_number,
                'truck_km' => $km,
                'truck_km_source' => $reading['source'] ?? 'manual',
                'oil_service' => true,
            ]);

            $alert->update(['work_order_id' => $workOrder->id]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Could not create the work order: '.$e->getMessage());
        }

        return back()->with('success', "Work order #{$workOrder->id} created for {$alert->truck_number}.");
    }

    /**
     * Correct a truck's service point or note.
     *
     * Mapon reminders are occasionally set up wrong, so the admin can move the
     * odometer the service falls due at. Remaining distance is derived from the
     * live reading, so it stays right as the truck keeps driving.
     */
    public function update(Request $request, TruckServiceAlert $alert)
    {
        $validated = $request->validate([
            'due_at_km' => ['required', 'integer', 'min:0', 'max:9999999'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $alert->update([
            'due_at_km' => $validated['due_at_km'],
            'note' => $validated['note'],
            // The figure is now the workshop's, not Mapon's, so the nightly run
            // measures against this rather than overwriting it.
            'source' => 'manual',
        ]);

        return back()->with('success', "{$alert->truck_number} updated.");
    }

    /**
     * Record the oil service and move the truck on to its next interval.
     */
    public function complete(Request $request, TruckServiceAlert $alert, OilServiceRecorder $recorder)
    {
        $validated = $request->validate([
            'km' => ['nullable', 'integer', 'min:0', 'max:9999999'],
        ]);

        // The worker on the linked job may close it out; otherwise admins only.
        $user = auth()->user();
        $isAssignedWorker = $alert->work_order_id
            && $alert->workOrder
            && $alert->workOrder->assigned_to === $user->id;

        if (! $user->isAdmin() && ! $isAssignedWorker) {
            abort(403, 'You cannot complete this service.');
        }

        try {
            $result = $recorder->record(
                $alert->truck_number,
                $validated['km'] ?? null,
                $alert->workOrder,
                $user->id
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Could not record the service: '.$e->getMessage());
        }

        if ($result === null) {
            return back()->with('error', 'No mileage available for this truck. Please enter the KM manually.');
        }

        return back()->with('success', sprintf(
            '%s serviced at %s km. Next service due at %s km.',
            $alert->truck_number,
            number_format($result['km']),
            number_format($result['due_at_km'])
        ));
    }

    /**
     * Full oil service history, optionally narrowed to one truck.
     */
    public function history(Request $request, MaponService $mapon)
    {
        $query = TruckOilService::with(['workOrder', 'recordedBy']);

        if ($request->filled('truck_number')) {
            $query->forTruck($request->truck_number);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('serviced_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('serviced_at', '<=', $request->date_to);
        }

        $services = $query->orderByDesc('serviced_at')
            ->paginate(25)
            ->withQueryString();

        $truckNumbers = TruckOilService::query()
            ->distinct()
            ->orderBy('truck_number')
            ->pluck('truck_number');

        return view('admin.trucks.oil-history', compact('services', 'truckNumbers'));
    }
}
