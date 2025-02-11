<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\StockAdjustment;

use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AdjustStockModal extends ModalComponent
{
    public Part $part;
    public int $currentStock;
    public ?int $adjustment = null;
    public string $notes = '';
    public string $adjustmentType = 'add';

    public function mount(int $partId)
    {
        $this->part = Part::findOrFail($partId);
        $this->currentStock = $this->part->stock;
    }

    public function rules()
    {
        return [
            'adjustment' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:255'],
            'adjustmentType' => ['required', 'in:add,remove'],
        ];
    }

    public function getNewStockProperty()
    {
        if (!$this->adjustment) {
            return $this->currentStock;
        }

        return $this->adjustmentType === 'add' 
            ? $this->currentStock + $this->adjustment 
            : $this->currentStock - $this->adjustment;
    }

    public function adjust()
    {
        $this->validate();
    
        if ($this->adjustmentType === 'remove' && $this->newStock < 0) {
            $this->addError('adjustment', 'Cannot remove more than current stock.');
            return;
        }
    
        try {
            //DB::beginTransaction();
    
            // Record the adjustment
            StockAdjustment::create([
                'part_id' => $this->part->id,
                'adjusted_by' => auth()->id(),
                'previous_stock' => $this->currentStock,
                'new_stock' => $this->newStock,
                'adjustment_quantity' => $this->adjustment,
                'adjustment_type' => $this->adjustmentType,
                'notes' => $this->notes
            ]);
    
            // Update the part stock
            $this->part->update([
                'stock' => $this->newStock
            ]);
    
            //DB::commit();
    
            $this->dispatch('stock-adjusted', [
                'message' => 'Stock adjusted successfully'
            ]);
    
            $this->closeModal();
    
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to adjust stock. Please try again.');
        }
    }
}