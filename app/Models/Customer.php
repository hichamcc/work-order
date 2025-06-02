<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the work orders for this customer.
     */
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get the default customer.
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Set this customer as default and unset others.
     */
    public function setAsDefault()
    {
        // Remove default from all other customers
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this customer as default
        $this->update(['is_default' => true]);
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one customer can be default
        static::saving(function ($customer) {
            if ($customer->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $customer->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}