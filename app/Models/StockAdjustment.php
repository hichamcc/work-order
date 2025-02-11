<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'part_id',
        'adjusted_by',
        'previous_stock',
        'new_stock',
        'adjustment_quantity',
        'adjustment_type',
        'notes'
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}