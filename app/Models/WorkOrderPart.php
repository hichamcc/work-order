<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderPart extends Model
{
    protected $fillable = [
        'work_order_id',
        'part_id',
        'quantity',
        'cost_at_time',
        'notes',
    ];

    protected $casts = [
        'cost_at_time' => 'decimal:2',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}