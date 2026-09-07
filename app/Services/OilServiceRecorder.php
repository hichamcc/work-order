<?php

namespace App\Services;

use App\Models\TruckOilService;
use App\Models\TruckServiceAlert;
use App\Models\WorkOrder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Records an oil service and rolls the truck on to its next interval.
 *
 * Both entry points use this: the button on the oil service list, and the
 * completion of a work order flagged as an oil service.
 */
class OilServiceRecorder
{
    public function __construct(protected MaponService $mapon)
    {
    }

    /**
     * Log the service, close any open alert, and open the next one.
     *
     * @param  int|null  $km  The odometer it was serviced at; falls back to the live Mapon reading.
     * @return array{km: int, due_at_km: int}|null  Null when no mileage could be determined.
     */
    public function record(
        string $truckNumber,
        ?int $km = null,
        ?WorkOrder $workOrder = null,
        ?int $recordedBy = null
    ): ?array {
        $reading = $this->mapon->readingForPlate($truckNumber, fresh: true);
        $openAlert = TruckServiceAlert::open()->where('truck_number', $truckNumber)->first();

        $km ??= $reading['km'] ?? $openAlert?->current_km;

        if ($km === null) {
            return null;
        }

        // A hand-entered figure that differs from Mapon is the operator's own.
        $source = $km !== $reading['km'] ? 'manual' : ($reading['source'] ?? 'manual');
        $interval = (int) config('services.mapon.oil_service_interval_km', 120000);
        $dueAtKm = $km + $interval;

        DB::transaction(function () use ($truckNumber, $km, $source, $workOrder, $recordedBy, $openAlert, $dueAtKm) {
            TruckOilService::create([
                'truck_number' => $truckNumber,
                'km' => $km,
                'km_source' => $source,
                'work_order_id' => $workOrder?->id,
                'recorded_by' => $recordedBy,
                'serviced_at' => now(),
            ]);

            $openAlert?->update([
                'resolved_at' => now(),
                'current_km' => $km,
                'note' => trim(($openAlert->note ? $openAlert->note."\n" : '')
                    .sprintf('Serviced at %s km on %s.', number_format($km), now()->format('d.m.Y'))),
            ]);

            // The truck is tracked from here on by its own service history. The
            // due figures are columns on the list, so the note starts empty for
            // the workshop to write into.
            TruckServiceAlert::create([
                'truck_number' => $truckNumber,
                'current_km' => $km,
                'last_service_km' => $km,
                'due_at_km' => $dueAtKm,
                'km_since_service' => 0,
                'km_source' => $source,
                'source' => 'service_history',
                'note' => null,
                'flagged_at' => now(),
            ]);
        });

        return ['km' => $km, 'due_at_km' => $dueAtKm];
    }

    /**
     * Record the oil service for a completed work order, if it calls for one.
     *
     * Safe to call on any completion: it does nothing unless the job is a truck
     * oil service that has not already been logged.
     */
    public function recordForWorkOrder(WorkOrder $workOrder, ?int $recordedBy = null): ?array
    {
        if (! $workOrder->isTruck() || ! $workOrder->oil_service || ! $workOrder->truck_number) {
            return null;
        }

        if (TruckOilService::where('work_order_id', $workOrder->id)->exists()) {
            return null;
        }

        try {
            return $this->record(
                $workOrder->truck_number,
                $workOrder->truck_km,
                $workOrder,
                $recordedBy ?? $workOrder->assigned_to
            );
        } catch (UniqueConstraintViolationException $e) {
            // A double submit got here first; its record stands.
            return null;
        }
    }
}
