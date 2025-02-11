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
</x-app-layout>