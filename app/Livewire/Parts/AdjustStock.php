<?php

namespace App\Livewire\Parts;

use App\Models\Part;
use App\Models\PartInstance;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AdjustStock extends ModalComponent
{

 
    public Part $part;
    public $currentStock;
    public $adjustmentType = 'add';
    public $adjustment = 1;
    public $notes = '';
    
    // Serial number related properties
    public $serialGeneration = 'auto';
    public $manualSerialNumbers = '';
    public $availableSerials = [];
    public $selectedSerials = [];
    
    public function mount($partId)
{

    $this->part = Part::findOrFail($partId);
    $this->currentStock = $this->part->stock;
    
    // Load available serial numbers if this is a serialized part
    if ($this->part->track_serials) {
        $this->availableSerials = $this->part->partInstances()
            ->where('status', 'in_stock')
            ->get();
    }
}
    
    public function getNewStockProperty()
    {
        if ($this->adjustmentType === 'add') {
            return $this->currentStock + $this->adjustment;
        } else {
            return $this->currentStock - $this->adjustment;
        }
    }
    

    

   /**
 * Determine if the submit button should be disabled
 *
 * @return bool
 */
public function getIsSubmitDisabledProperty()
{
    // Check for negative stock
    if ($this->newStock < 0) {
        return true;
    }
    
    // Check for serialized part validations
    if ($this->part->track_serials) {
        if ($this->adjustmentType === 'add') {
            if ($this->serialGeneration === 'manual' && $this->getManualSerialNumbersCountProperty() !== $this->adjustment) {
                return true;
            }
        } else {
            if (count($this->selectedSerials) !== $this->adjustment) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get the count of available serialized parts that can be removed
 *
 * @return int
 */
public function getAvailableSerialCountProperty()
{
    if (!$this->part->track_serials) {
        return $this->part->stock;
    }
    
    return $this->availableSerials->count();
}


/**
 * Calculate the manual serial numbers count
 *
 * @return int
 */
public function getManualSerialNumbersCountProperty()
{
    if (empty($this->manualSerialNumbers)) {
        return 0;
    }
    
    // Count non-empty lines
    $lines = array_filter(explode("\n", $this->manualSerialNumbers), function($line) {
        return trim($line) !== '';
    });
    
    return count($lines);
}
    
    public function getNextSerialNumberPreview()
    {
        $prefix = strtoupper(substr($this->part->part_number, 0, 3));
        $lastInstance = $this->part->partInstances()->orderBy('id', 'desc')->first();
        
        if ($lastInstance) {
            $lastNumber = (int)substr($lastInstance->serial_number, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }
    
    public function getLastSerialNumberPreview()
    {
        $prefix = strtoupper(substr($this->part->part_number, 0, 3));
        $lastInstance = $this->part->partInstances()->orderBy('id', 'desc')->first();
        
        if ($lastInstance) {
            $lastNumber = (int)substr($lastInstance->serial_number, 4);
            $newNumber = $lastNumber + $this->adjustment;
        } else {
            $newNumber = $this->adjustment;
        }
        
        return $prefix . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }
    
    public function rules()
    {
        return [
            'adjustmentType' => 'required|in:add,remove',
            'adjustment' => 'required|integer|min:1',
            'notes' => 'required|string|max:255',
            'manualSerialNumbers' => 'nullable|string',
            'selectedSerials' => 'nullable|array',
        ];
    }
    
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        
        // Reset selected serials when changing adjustment quantity
        if ($propertyName === 'adjustment' && $this->part->track_serials && $this->adjustmentType === 'remove') {
            $this->selectedSerials = [];
        }
    }
    
    public function adjust()
    {
        $this->validate();
        
        // Don't allow reducing below zero
        if ($this->newStock < 0) {
            $this->addError('adjustment', 'Cannot reduce stock below zero');
            return;
        }
        
        // For serialized parts, validate serial numbers
        if ($this->part->track_serials) {
            if ($this->adjustmentType === 'add' && $this->serialGeneration === 'manual') {
                $serialNumbers = array_filter(explode("\n", $this->manualSerialNumbers), 'trim');
                
                if (count($serialNumbers) !== $this->adjustment) {
                    $this->addError('manualSerialNumbers', 'Number of serial numbers must match quantity');
                    return;
                }
                
                // Check for duplicates in input
                $uniqueSerials = array_unique($serialNumbers);
                if (count($uniqueSerials) !== count($serialNumbers)) {
                    $this->addError('manualSerialNumbers', 'Duplicate serial numbers detected');
                    return;
                }
                
                // Check for existing serial numbers
                foreach ($serialNumbers as $serial) {
                    $exists = PartInstance::where('part_id', $this->part->id)
                        ->where('serial_number', trim($serial))
                        ->exists();
                    
                    if ($exists) {
                        $this->addError('manualSerialNumbers', "Serial number '{$serial}' already exists");
                        return;
                    }
                }
            } elseif ($this->adjustmentType === 'remove') {
                if (count($this->selectedSerials) !== $this->adjustment) {
                    $this->addError('selectedSerials', 'Please select exactly ' . $this->adjustment . ' serial numbers');
                    return;
                }
            }
        }
        
        DB::beginTransaction();
        
        try {

            // Handle serialized parts
            if ($this->part->track_serials) {
                if ($this->adjustmentType === 'add') {
                    if ($this->serialGeneration === 'auto') {
                        // Auto-generate serial numbers
                        for ($i = 0; $i < $this->adjustment; $i++) {
                            PartInstance::create([
                                'part_id' => $this->part->id,
                                'serial_number' => $this->part->generateSerialNumber(),
                                'status' => 'in_stock',
                            ]);
                        }
                    } else {
                        // Use manually entered serial numbers
                        $serialNumbers = array_filter(explode("\n", $this->manualSerialNumbers), 'trim');
                        
                        foreach ($serialNumbers as $serial) {
                            PartInstance::create([
                                'part_id' => $this->part->id,
                                'serial_number' => trim($serial),
                                'status' => 'in_stock',
                            ]);
                        }
                    }
                } else {
                    // Remove selected serial numbers
                    foreach ($this->selectedSerials as $instanceId) {
                        $instance = PartInstance::findOrFail($instanceId);
                        $instance->delete();
                    }
                }
            }
            
            // Create stock adjustment record
            StockAdjustment::create([
                'part_id' => $this->part->id,
                'adjusted_by' => auth()->id(),
                'previous_stock' => $this->currentStock,
                'new_stock' => $this->newStock,
                'adjustment_quantity' => $this->adjustmentType === 'add' 
                    ? $this->adjustment 
                    : -$this->adjustment,
                'adjustment_type' => $this->adjustmentType === 'add' ? 'add' : 'remove',
                'notes' => $this->notes,
            ]);
            
            // Update the part's stock
            
            $this->part->stock = $this->newStock;
            $this->part->save();
            
            DB::commit();
            
            $this->dispatch('stock-adjusted', [
                'message' => 'Stock adjusted successfully',
                'part_id' => $this->part->id
            ]);
            
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollBack();            
            session()->flash('error', 'Error adjusting stock: ' . $e->getMessage());
            $this->closeModal();
        }
    }
    
    public static function modalMaxWidth(): string
    {
        return 'md';
    }
}