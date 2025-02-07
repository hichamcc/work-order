<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_template_id',
        'version',
        'name',
        'description',
        'checklist_items',
        'created_by',
        'created_at',
        'change_notes',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'created_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(ServiceTemplate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}