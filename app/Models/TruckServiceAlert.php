<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TruckServiceAlert extends Model
{
    protected $fillable = [
        'truck_number',
        'work_order_id',
        'current_km',
        'last_service_km',
        'due_at_km',
        'km_since_service',
        'km_source',
        'source',
        'note',
        'flagged_at',
        'resolved_at',
    ];

    protected $casts = [
        'current_km' => 'integer',
        'last_service_km' => 'integer',
        'due_at_km' => 'integer',
        'km_since_service' => 'integer',
        'flagged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * How far the truck is from its service point. Negative once it is past it.
     */
    public function getKmRemainingAttribute(): ?int
    {
        if ($this->due_at_km === null || $this->current_km === null) {
            return null;
        }

        return $this->due_at_km - $this->current_km;
    }

    /**
     * Trucks within this distance of their service point are worth booking in.
     */
    const DUE_SOON_KM = 10000;

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Where the truck stands: past its service point, close to it, or fine.
     */
    public function getServiceStateAttribute(): string
    {
        $remaining = $this->km_remaining;

        if ($remaining === null) {
            return 'due';
        }

        if ($remaining <= 0) {
            return 'overdue';
        }

        return $remaining <= self::DUE_SOON_KM ? 'due_soon' : 'upcoming';
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Only trucks at or past their service point.
     */
    public function scopeOverdue($query)
    {
        return $query->whereRaw('CAST(current_km AS SIGNED) >= CAST(due_at_km AS SIGNED)');
    }

    /**
     * Overdue, plus those closing in on the service point.
     */
    public function scopeNeedsAttention($query, int $within = self::DUE_SOON_KM)
    {
        return $query->whereRaw('CAST(current_km AS SIGNED) >= CAST(due_at_km AS SIGNED) - ?', [$within]);
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    /**
     * Close any open alert for a truck, called once a new oil service lands.
     */
    public static function resolveFor(string $truckNumber): void
    {
        static::open()
            ->where('truck_number', $truckNumber)
            ->update(['resolved_at' => now()]);
    }
}
