<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderTime extends Model
{
    protected $fillable = [
        'work_order_id',
        'user_id',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}