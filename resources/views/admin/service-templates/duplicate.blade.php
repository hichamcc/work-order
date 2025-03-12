{{-- resources/views/admin/service-templates/duplicate.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Duplicate Service Template') }}
            </h2>
            <a href="{{ route('admin.service-templates.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Templates') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-lg font-medium text-blue-800">Duplicating: {{ $oldName }}</h3>
                    <p class="text-sm text-blue-600 mt-1">You are creating a new template based on this one. Modify the details below as needed.</p>
                </div>

                <form method="POST" action="{{ route('admin.service-templates.store-duplicate', $template) }}" class="space-y-6">
                    @csrf

                    <!-- Template Information -->
                    <div>
                        <x-input-label for="name" :value="__('Template Name')" />
                        <input type="text" 
                               name="name" 
                               id="name"
                               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                               value="{{ old('name', $template->name) }}" 
                               required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" 
                                name="description" 
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3">{{ old('description', $template->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" 
                                name="category_id" 
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $template->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                   value="1" 
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                        </label>
                    </div>

                    <!-- Checklist Items Preview -->
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Checklist Items to be Duplicated') }}</h3>
                        
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                            @forelse($template->checklistItems as $index => $item)
                                <div class="p-3 bg-white rounded border">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">{{ $index + 1 }}. {{ $item->description }}</h4>
                                        <div class="flex space-x-2">
                                            @if($item->requires_photo)
                                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">Photo Required</span>
                                            @endif
                                            @if($item->is_required)
                                                <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">Required</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($item->instructions)
                                        <p class="mt-1 text-sm text-gray-600">{{ $item->instructions }}</p>
                                    @endif
                                    
                                    @if($item->file_instructions)
                                        <div class="mt-2 flex items-center">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="ml-2 text-xs text-gray-500">
                                                Has instruction file: {{ basename($item->file_instructions) }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($item->photo_instructions)
                                        <p class="mt-1 text-xs text-indigo-600">Photo Instructions: {{ $item->photo_instructions }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-3">No checklist items to duplicate.</p>
                            @endforelse
                        </div>
                        
                        <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                            <p class="text-yellow-700 text-sm">
                                <strong>Note:</strong> All checklist items from the original template will be duplicated, including instructions and files. You can edit them after duplication.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <x-primary-button>
                            {{ __('Create Duplicate Template') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>