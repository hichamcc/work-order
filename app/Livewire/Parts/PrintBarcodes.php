<?php

namespace App\Livewire\Parts;

use App\Models\Part;
use App\Models\PartInstance;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class PrintBarcodes extends ModalComponent
{
    public Part $part;
    public $availableSerials = [];
    public $selectedSerials = [];
    public $selectAll = false;
    
    public function mount($partId)
    {
        $this->part = Part::findOrFail($partId);
        
        // Load available serials
        $this->availableSerials = $this->part->partInstances()
            ->where('status', 'in_stock')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedSerials = $this->availableSerials->pluck('id')->toArray();
        } else {
            $this->selectedSerials = [];
        }
    }
    
    public function print()
    {
        if (empty($this->selectedSerials)) {
            session()->flash('error', 'Please select at least one serial number to print');
            return;
        }
        
        // These IDs will be used to generate the barcodes on the print page
        $serialIdsString = implode(',', $this->selectedSerials);
        
        // Redirect to a print view that will handle the barcode generation
        return redirect()->route('admin.parts.print-barcodes', [
            'part' => $this->part->id,
            'serials' => $serialIdsString
        ]);
    }
    
    public static function modalMaxWidth(): string
    {
        return '3xl';
    }
    

    public function render()
    {
        return view('livewire.parts.print-barcodes');
    }
}