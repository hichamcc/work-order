<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->role->slug === 'admin';
    }

    public function isWorker()
    {
        return $this->role->slug === 'worker';
    }
    public function assignedWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'assigned_to');
    }


    public function workOrderTimes()
    {
        return $this->hasMany(WorkOrderTime::class);
    }


public function helperWorkOrders()
{
    return $this->belongsToMany(WorkOrder::class, 'work_order_helpers')
                ->withTimestamps()
                ->withPivot('notes');
}

//  get all work orders (assigned as primary + helper)
public function allWorkOrders()
{
    $primaryWorkOrders = $this->workOrders; // Assuming you have this relationship already
    $helperWorkOrders = $this->helperWorkOrders;
    
    return $primaryWorkOrders->concat($helperWorkOrders)->unique('id');
}

}
