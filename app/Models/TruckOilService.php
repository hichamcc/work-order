<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TruckOilService extends Model
{
    protected $fillable = [
        'truck_number',
        'km',
        'km_source',
        'work_order_id',
        'recorded_by',
        'serviced_at',
    ];

    protected $casts = [
        'km' => 'integer',
        'serviced_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeForTruck($query, string $truckNumber)
    {
        return $query->where('truck_number', $truckNumber);
    }

    /**
     * The most recent oil service recorded for each truck, keyed by truck number.
     */
    public static function latestPerTruck()
    {
        return static::query()
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('truck_oil_services')
                    ->groupBy('truck_number');
            })
            ->get()
            ->keyBy('truck_number');
    }
}
