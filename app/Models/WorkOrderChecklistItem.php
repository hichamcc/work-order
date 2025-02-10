<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistItem extends Model
{
    protected $fillable = [
        'work_order_id',
        'checklist_item_id',
        'is_completed',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function photos()
    {
        return $this->hasMany(WorkOrderPhoto::class, 'checklist_item_id');
    }
}