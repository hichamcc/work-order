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
        'is_active',
        'track_serials'
    ];

    protected $casts = [
        'stock' => 'integer',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        'track_serials' => 'boolean'
    ];

    public function workOrderParts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }
    
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }
    
    public function partInstances()
    {
        return $this->hasMany(PartInstance::class);
    }
    
    /**
     * Generate a sequential serial number for this part
     * Uses format: PPP00000001 where PPP is the first 3 chars of the part number
     */
    public function generateSerialNumber()
    {
        $prefix = strtoupper(substr($this->part_number, 0, 3));
        $lastInstance = $this->partInstances()->orderBy('id', 'desc')->first();
        
        if ($lastInstance) {
            // Extract the numeric part and increment
            $lastNumber = (int)substr($lastInstance->serial_number, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }
}