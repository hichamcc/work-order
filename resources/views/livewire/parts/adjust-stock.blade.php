{{-- resources/views/livewire/parts/adjust-stock.blade.php --}}
<div class="p-6">
    <div class="mb-4">
        <h2 class="text-lg font-medium text-gray-900">
            Adjust Stock: {{ $part->name }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Current stock: {{ $currentStock }} units
        </p>
        @if($part->track_serials)
            <span class="px-2 mt-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                Serial numbers tracked
            </span>
        @endif
    </div>

    <form wire:submit="adjust" class="space-y-4">
        <!-- Adjustment Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Adjustment Type</label>
            <div class="mt-2 flex space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" 
                           wire:model.live="adjustmentType" 
                           value="add" 
                           class="form-radio text-indigo-600">
                    <span class="ml-2">Add Stock</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" 
                           wire:model.live="adjustmentType" 
                           value="remove" 
                           class="form-radio text-indigo-600">
                    <span class="ml-2">Remove Stock</span>
                </label>
            </div>
            @error('adjustmentType') 
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Quantity -->
        <div>
            <label for="adjustment" class="block text-sm font-medium text-gray-700">
                Quantity to {{ $adjustmentType === 'add' ? 'Add' : 'Remove' }}
            </label>
            <input type="number" 
                   id="adjustment"
                   wire:model.live="adjustment"
                   min="1"
                   @if($adjustmentType === 'remove' && $part->track_serials)
                   max="{{ $this->availableSerialCount }}"
                   @endif
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('adjustment') 
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Serial Number Section (Only for serialized parts) -->
        @if($part->track_serials)
            @if($adjustmentType === 'add')
                <div class="border rounded-md p-3 space-y-3">
                    <h3 class="text-sm font-medium text-gray-700">Serial Numbers</h3>
                    
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                wire:model.live="serialGeneration" 
                                value="auto" 
                                class="form-radio text-indigo-600">
                            <span class="ml-2 text-sm">Auto-generate serial numbers</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                wire:model.live="serialGeneration" 
                                value="manual" 
                                class="form-radio text-indigo-600">
                            <span class="ml-2 text-sm">Enter serial numbers manually</span>
                        </label>
                    </div>
                    
                    @if($serialGeneration === 'manual')
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">
                                Enter {{ $adjustment }} serial numbers (one per line):
                            </label>
                            <textarea
                                wire:model="manualSerialNumbers"
                                rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Enter serial numbers, one per line"></textarea>
                            
                            @if($manualSerialNumbersCount > 0 && $manualSerialNumbersCount !== $adjustment)
                                <p class="mt-1 text-xs text-amber-600">
                                    You've entered {{ $manualSerialNumbersCount }} numbers, but quantity is {{ $adjustment }}
                                </p>
                            @endif
                            
                            @error('manualSerialNumbers') 
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    
                    @if($serialGeneration === 'auto' && $adjustment > 0)
                        <div class="text-xs text-gray-500">
                            Will generate: {{ $this->getNextSerialNumberPreview() }}{{ $adjustment > 1 ? ' through ' . $this->getLastSerialNumberPreview() : '' }}
                        </div>
                    @endif
                </div>
            @else
                <div class="border rounded-md p-3">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Select Serial Numbers to Remove</h3>
                    
                    @if(count($availableSerials) > 0)
                        <div class="max-h-40 overflow-y-auto">
                            @foreach($availableSerials as $instance)
                                <div class="mb-1">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" 
                                            wire:model.live="selectedSerials" 
                                            value="{{ $instance->id }}" 
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm">{{ $instance->serial_number }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(count($selectedSerials) !== $adjustment)
                            <p class="mt-2 text-xs text-amber-600">
                                Please select exactly {{ $adjustment }} serial numbers
                            </p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 italic">No available serial numbers to remove.</p>
                    @endif
                </div>
            @endif
        @endif

        <!-- New Stock Preview -->
        <div class="bg-gray-50 px-4 py-3 rounded-md">
            <div class="text-sm text-gray-700">
                New Stock Level: 
                <span class="font-medium {{ $this->newStock < 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $this->newStock }}
                </span>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">
                Notes
            </label>
            <textarea id="notes"
                      wire:model="notes"
                      rows="2"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Reason for adjustment..."></textarea>
            @error('notes') 
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Footer -->
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button"
                    wire:click="$dispatch('closeModal')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                Cancel
            </button>
            <button type="submit"
                @if($this->isSubmitDisabled)
                disabled
                @endif
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
            Adjust Stock
        </button>
        </div>
    </form>
</div>