<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChecklistItem extends Model
{
    protected $fillable = [
        'service_template_id',
        'description',
        'instructions',
        'photo_instructions',
        'file_instructions', // New field for file path
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

    protected $appends = [
        'file_instructions_url',
    ];

    public function template()
    {
        return $this->belongsTo(ServiceTemplate::class, 'service_template_id');
    }
    
    // Helper method to get the download URL for the instruction file
    public function getFileInstructionsUrlAttribute()
    {
        if ($this->file_instructions) {
            return Storage::url($this->file_instructions);
        }
        
        return null;
    }
    
    // Method to handle file upload
    public function uploadFileInstructions($file)
    {
        // Delete existing file if present
        if ($this->file_instructions && Storage::exists($this->file_instructions)) {
            Storage::delete($this->file_instructions);
        }
        
        // Store the new file
        $path = $file->store('checklist-instructions', 'public');
        $this->update(['file_instructions' => $path]);
        
        return $path;
    }
    
    // Method to delete the file
    public function deleteFileInstructions()
    {
        if ($this->file_instructions && Storage::exists($this->file_instructions)) {
            Storage::delete($this->file_instructions);
            $this->update(['file_instructions' => null]);
            return true;
        }
        
        return false;
    }
}