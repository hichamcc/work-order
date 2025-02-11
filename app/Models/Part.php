<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
        'name',
        'part_number',
        'description',
        'stock',
        'cost',
        'is_active'
    ];

    protected $casts = [
        'stock' => 'integer',
        'cost' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function workOrderParts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }
    public function stockAdjustments()
{
    return $this->hasMany(StockAdjustment::class);
}
}