{{-- resources/views/admin/parts/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Part') }}
            </h2>
            <a href="{{ route('admin.parts.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Parts') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <form method="POST" action="{{ route('admin.parts.store') }}" class="p-6">
                    @csrf

                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Part Name')" />
                                <x-text-input id="name" 
                                            name="name" 
                                            type="text" 
                                            class="mt-1 block w-full" 
                                            :value="old('name')" 
                                            required 
                                            autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="part_number" :value="__('Part Number')" />
                                <x-text-input id="part_number" 
                                            name="part_number" 
                                            type="text" 
                                            class="mt-1 block w-full" 
                                            :value="old('part_number')" 
                                            required />
                                <x-input-error :messages="$errors->get('part_number')" class="mt-2" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" 
                                        name="description" 
                                        rows="3"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="stock" :value="__('Initial Stock')" />
                                <x-text-input id="stock" 
                                            name="stock" 
                                            type="number" 
                                            class="mt-1 block w-full" 
                                            :value="old('stock', 0)" 
                                            required 
                                            min="0" />
                                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="cost" :value="__('Cost Per Unit')" />
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <x-text-input id="cost" 
                                                name="cost" 
                                                type="number" 
                                                class="pl-7 block w-full" 
                                                :value="old('cost')" 
                                                required 
                                                step="0.01" 
                                                min="0" />
                                </div>
                                <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                            </div>

                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                           value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                </label>
                                <p class="mt-1 text-sm text-gray-500">Active parts can be used in work orders</p>
                            </div>
                            
                            <!-- Serial Number Tracking Options -->
                            <div class="col-span-2">
                                <div class="p-4 border border-gray-200 rounded-md">
                                    <div class="mb-4">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   id="track_serials"
                                                   name="track_serials" 
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                                   value="1"
                                                   {{ old('track_serials') ? 'checked' : '' }}
                                                   onchange="toggleSerialOptions()">
                                            <span class="ml-2 text-md font-medium text-gray-700">{{ __('Track Serial Numbers') }}</span>
                                        </label>
                                        <p class="mt-1 text-sm text-gray-500">Enable this to track individual parts with unique serial numbers</p>
                                    </div>
                                    
                                    <div id="serial_options" class="ml-6 pl-2 border-l-2 border-gray-200" style="display: none;">
                                        <p class="mb-2 font-medium">Serial Number Generation:</p>
                                        
                                        <div class="mb-3">
                                            <label class="inline-flex items-center mb-2">
                                                <input type="radio" 
                                                       name="serial_generation_type" 
                                                       value="automatic"
                                                       class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                       {{ old('serial_generation_type', 'automatic') === 'automatic' ? 'checked' : '' }}
                                                       onchange="toggleSerialOptions()">
                                                <span class="ml-2 text-sm text-gray-700">{{ __('Automatic Generation') }}</span>
                                            </label>
                                            <p class="ml-6 text-xs text-gray-500">System will generate sequential serial numbers based on part number</p>
                                        </div>
                                        
                                        <div>
                                            <label class="inline-flex items-center mb-2">
                                                <input type="radio" 
                                                       name="serial_generation_type" 
                                                       value="manual"
                                                       class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                       {{ old('serial_generation_type') === 'manual' ? 'checked' : '' }}
                                                       onchange="toggleSerialOptions()">
                                                <span class="ml-2 text-sm text-gray-700">{{ __('Manual Entry') }}</span>
                                            </label>
                                            <p class="ml-6 text-xs text-gray-500">You'll need to manually enter serial numbers for each item</p>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" 
                                                    name="generate_barcodes" 
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                                    value="1"
                                                    {{ old('generate_barcodes', true) ? 'checked' : '' }}>
                                                <span class="ml-2 text-sm text-gray-700">{{ __('Generate Printable Barcodes') }}</span>
                                            </label>
                                            <p class="ml-6 text-xs text-gray-500">Create barcodes for each serial number for easy scanning</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                {{ __('Create Part') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSerialOptions() {
            const trackSerials = document.getElementById('track_serials').checked;
            const serialOptions = document.getElementById('serial_options');
            
            if (trackSerials) {
                serialOptions.style.display = 'block';
            } else {
                serialOptions.style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleSerialOptions();
        });
    </script>
</x-app-layout>