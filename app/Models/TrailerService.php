<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * History of services carried out on a trailer, so the workshop can look back
 * the same way it can for a truck's oil services.
 */
class TrailerService extends Model
{
    protected $fillable = [
        'trailer_id',
        'work_order_id',
        'recorded_by',
        'note',
        'serviced_at',
    ];

    protected $casts = [
        'serviced_at' => 'datetime',
    ];

    public function trailer()
    {
        return $this->belongsTo(Trailer::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
