<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderPhoto extends Model
{
    protected $fillable = [
        'work_order_id',
        'checklist_item_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'uploaded_by',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}