<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TruckServiceAlert extends Model
{
    protected $fillable = [
        'truck_number',
        'current_km',
        'last_service_km',
        'km_since_service',
        'km_source',
        'note',
        'flagged_at',
        'resolved_at',
    ];

    protected $casts = [
        'current_km' => 'integer',
        'last_service_km' => 'integer',
        'km_since_service' => 'integer',
        'flagged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function scopeOpen($query)
    {
        return $query->whereNull('resolved_at');
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
