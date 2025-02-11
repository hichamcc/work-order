<div class="p-6">
    <div class="mb-4">
        <h2 class="text-lg font-medium text-gray-900">
            Adjust Stock: {{ $part->name }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Current stock: {{ $currentStock }} units
        </p>
    </div>

    <form wire:submit="adjust" class="space-y-4">
        <!-- Adjustment Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Adjustment Type</label>
            <div class="mt-2 flex space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" 
                           wire:model="adjustmentType" 
                           value="add" 
                           class="form-radio text-indigo-600">
                    <span class="ml-2">Add Stock</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" 
                           wire:model="adjustmentType" 
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
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('adjustment') 
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

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
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Adjust Stock
            </button>
        </div>
    </form>
</div>