<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TruckServiceAlert;
use App\Services\MaponService;
use Illuminate\Http\Request;

/**
 * Read-only oil service status for other systems.
 *
 * The delivery system colours its truck numbers from this: red once a truck is
 * past its service point, yellow as it approaches, nothing otherwise.
 */
class TruckServiceStatusController extends Controller
{
    /**
     * Every truck currently being tracked, keyed by truck number.
     */
    public function index(Request $request)
    {
        $alerts = TruckServiceAlert::open()
            ->whereNotNull('due_at_km')
            ->orderBy('truck_number')
            ->get();

        // Callers that only want to highlight can skip the healthy trucks.
        $onlyFlagged = $request->boolean('flagged');

        $trucks = $alerts
            ->map(fn (TruckServiceAlert $alert) => $this->present($alert))
            ->when($onlyFlagged, fn ($items) => $items->filter(fn ($item) => $item['colour'] !== null))
            ->keyBy('truck_number');

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'interval_km' => (int) config('services.mapon.oil_service_interval_km', 120000),
            'due_soon_km' => TruckServiceAlert::DUE_SOON_KM,
            'count' => $trucks->count(),
            'trucks' => $trucks,
        ]);
    }

    /**
     * One truck. Accepts the full plate or the leading segment.
     */
    public function show(string $truckNumber, MaponService $mapon)
    {
        $normalised = $mapon->normalisePlate($truckNumber);

        $alert = TruckServiceAlert::open()
            ->whereNotNull('due_at_km')
            ->get()
            ->first(function (TruckServiceAlert $alert) use ($mapon, $normalised) {
                $stored = $mapon->normalisePlate($alert->truck_number);

                if ($stored === $normalised) {
                    return true;
                }

                // The delivery system may hold only the tractor plate, while this
                // one stores the composite "ZK4695L/WT73/4154" form.
                foreach (['/', '-'] as $separator) {
                    if (str_contains($stored, $separator)) {
                        return explode($separator, $stored)[0] === $normalised;
                    }
                }

                return false;
            });

        if (! $alert) {
            return response()->json([
                'truck_number' => $truckNumber,
                'tracked' => false,
                'colour' => null,
            ], 404);
        }

        return response()->json($this->present($alert));
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(TruckServiceAlert $alert): array
    {
        $state = $alert->service_state;

        return [
            'truck_number' => $alert->truck_number,
            'tracked' => true,
            'state' => $state,
            'colour' => match ($state) {
                'overdue' => 'red',
                'due_soon' => 'yellow',
                default => null,
            },
            'current_km' => $alert->current_km,
            'due_at_km' => $alert->due_at_km,
            'km_remaining' => $alert->km_remaining,
            'last_checked_at' => $alert->flagged_at?->toIso8601String(),
        ];
    }
}
