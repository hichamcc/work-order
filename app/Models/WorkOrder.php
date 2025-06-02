<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'title',
        'description',
        'service_template_id',
        'assigned_to',
        'created_by',
        'status',
        'priority',
        'started_at',
        'completed_at',
        'due_date',
        'hold_reason',
        'invoiced',
        'customer_id',


    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'datetime',
        'invoiced' => 'boolean',

    ];


    

    public function serviceTemplate()
    {
        return $this->belongsTo(ServiceTemplate::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function times()
    {
        return $this->hasMany(WorkOrderTime::class);
    }

    public function parts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function checklistItems()
    {
        return $this->hasMany(WorkOrderChecklistItem::class);
    }

    public function photos()
    {
        return $this->hasMany(WorkOrderPhoto::class);
    }

    public function comments()
    {
        return $this->hasMany(WorkOrderComment::class);
    }

    
public function helpers()
{
    return $this->belongsToMany(User::class, 'work_order_helpers')
                ->withTimestamps()
                ->withPivot('notes');
}

// get all workers (primary + helpers)
public function allWorkers()
{
    $primaryWorker = $this->assignedTo;
    $helpers = $this->helpers;
    
    if ($primaryWorker) {
        return $helpers->push($primaryWorker)->unique('id');
    }
    
    return $helpers;
}
/**
 * Get the customer that owns the work order.
 */
public function customer()
{
    return $this->belongsTo(Customer::class);
}

// Optional: Add a scope to filter by customer
public function scopeForCustomer($query, $customerId)
{
    return $query->where('customer_id', $customerId);
}
}