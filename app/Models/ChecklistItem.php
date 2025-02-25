<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'service_template_id',
        'description',
        'instructions',
        'photo_instructions',
        'order',
        'requires_photo',
        'is_required',
        'additional_fields',
    ];

    protected $casts = [
        'requires_photo' => 'boolean',
        'is_required' => 'boolean',
        'additional_fields' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(ServiceTemplate::class, 'service_template_id');
    }
}