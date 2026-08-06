<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TruckOilService;
use App\Models\TruckServiceAlert;
use App\Services\MaponService;
use Illuminate\Http\Request;

class TruckServiceController extends Controller
{
    /**
     * Trucks the nightly check has flagged as needing an oil service.
     */
    public function index(Request $request)
    {
        $query = TruckServiceAlert::query();

        if ($request->boolean('show_resolved')) {
            $query->resolved();
        } else {
            $query->open();
        }

        if ($request->filled('search')) {
            $query->where('truck_number', 'like', "%{$request->search}%");
        }

        $alerts = $query->orderByDesc('km_since_service')
            ->paginate(25)
            ->withQueryString();

        $intervalKm = (int) config('services.mapon.oil_service_interval_km', 120000);

        return view('admin.trucks.service-due', compact('alerts', 'intervalKm'));
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
