{{-- resources/views/livewire/parts/print-barcodes.blade.php --}}
<div class="p-6">
    <div class="mb-4">
        <h2 class="text-lg font-medium text-gray-900">
            Print Barcodes: {{ $part->name }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Select the serial numbers you want to print barcodes for
        </p>
    </div>

    <div class="mb-4">
        <label class="inline-flex items-center">
            <input type="checkbox" 
                   wire:model.live="selectAll" 
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="ml-2 font-medium">Select All</span>
        </label>
    </div>

    <div class="border rounded-md mb-4">
        <div class="max-h-96 overflow-y-auto p-4">
            @if(count($availableSerials) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-2">
                    @foreach($availableSerials as $instance)
                        <div class="border rounded-md p-2 flex items-center">
                            <input type="checkbox" 
                                   wire:model.live="selectedSerials" 
                                   value="{{ $instance->id }}" 
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2">{{ $instance->serial_number }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-4">No serial numbers available</p>
            @endif
        </div>
    </div>

    <div class="mb-4 bg-gray-50 p-3 rounded-md">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700">Selected: <span class="font-medium">{{ count($selectedSerials) }}</span> of {{ count($availableSerials) }}</span>
            <span class="text-sm text-gray-700" x-data="{ show: {{ count($selectedSerials) > 0 ? 'true' : 'false' }} }" x-show="show">
                Ready to print
            </span>
        </div>
    </div>

    <div class="flex justify-end space-x-3">
        <button type="button"
                wire:click="$dispatch('closeModal')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
            Cancel
        </button>
        <button type="button"
                wire:click="print"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                @if(count($selectedSerials) === 0) disabled @endif>
            Print Barcodes
        </button>
    </div>
</div>