{{-- resources/views/admin/parts/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Part') }}: {{ $part->name }}
            </h2>
            <a href="{{ route('admin.parts.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Parts') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <!-- Current Stats -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Current Stock</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $part->stock }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Total Value</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($part->stock * $part->cost, 2) }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Status</div>
                            <div class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $part->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $part->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.parts.update', $part) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="name" :value="__('Part Name')" />
                                    <x-text-input id="name" 
                                                name="name" 
                                                type="text" 
                                                class="mt-1 block w-full" 
                                                :value="old('name', $part->name)" 
                                                required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="part_number" :value="__('Part Number')" />
                                    <x-text-input id="part_number" 
                                                name="part_number" 
                                                type="text" 
                                                class="mt-1 block w-full" 
                                                :value="old('part_number', $part->part_number)" 
                                                required />
                                    <x-input-error :messages="$errors->get('part_number')" class="mt-2" />
                                </div>

                                <div class="col-span-2">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" 
                                            name="description" 
                                            rows="3"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $part->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="stock" :value="__('Current Stock')" />
                                    <x-text-input id="stock" 
                                                name="stock" 
                                                type="number" 
                                                class="mt-1 block w-full" 
                                                :value="old('stock', $part->stock)" 
                                                required 
                                                min="0" />
                                    <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500">
                                        Use the "Adjust Stock" button for inventory adjustments
                                    </p>
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
                                                    :value="old('cost', $part->cost)" 
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
                                               {{ old('is_active', $part->is_active) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                    </label>
                                    <p class="mt-1 text-sm text-gray-500">Active parts can be used in work orders</p>
                                </div>
                            </div>

                            <!-- Usage Information -->
                            @if($part->workOrderParts->count() > 0)
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Usage History</h3>
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    This part has been used in {{ $part->workOrderParts->count() }} work orders.
                                                    Some fields may be restricted from editing.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-end space-x-4">
                                <button type="button" 
                                        onclick="Livewire.dispatch('openModal', { component: 'adjust-stock-modal', arguments: { partId: {{ $part->id }} }})"
                                        class="inline-flex hidden items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    {{ __('Adjust Stock') }}
                                </button>

                                <x-primary-button>
                                    {{ __('Update Part') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>