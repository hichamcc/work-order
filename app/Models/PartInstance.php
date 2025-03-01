<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PartInstance extends Model
{
    protected $fillable = [
        'part_id',
        'serial_number',
        'status', // 'in_stock', 'assigned', 'used'
        'work_order_id',
        'notes'
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
    
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
    
    // Generate barcode data URI
    public function getBarcodeAttribute()
    {
        // This is a placeholder. You would replace this with your actual barcode generation code
        // or call to a barcode service/library
        return route('admin.parts.barcode', $this->id);
    }
}