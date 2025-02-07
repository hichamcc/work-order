{{-- resources/views/admin/service-templates/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Service Template') }}
            </h2>
            <a href="{{ route('admin.service-templates.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Templates') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <form method="POST" action="{{ route('admin.service-templates.update', $template) }}" class="p-6" x-data="templateForm()">
                    @csrf
                    @method('PUT')

                    <!-- Template Information -->
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="name" :value="__('Template Name')" />
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                   value="{{ old('name', $template->name) }}" 
                                   required />
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

                        <!-- Checklist Items -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Checklist Items') }}</h3>
                                <button type="button" @click="addItem"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    {{ __('Add Item') }}
                                </button>
                            </div>

                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="flex justify-between mb-4">
                                        <h4 class="text-md font-medium text-gray-700">Item #<span x-text="index + 1"></span></h4>
                                        <button type="button" @click="removeItem(index)" 
                                            class="text-red-600 hover:text-red-800">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label :value="__('Description')" />
                                            <input type="text" 
                                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                   x-model="item.description" 
                                                   :name="`checklist_items[${index}][description]`"
                                                   required />
                                            <input type="hidden" 
                                                   x-bind:name="`checklist_items[${index}][id]`" 
                                                   x-bind:value="item.id" />
                                        </div>

                                        <div>
                                            <x-input-label :value="__('Instructions')" />
                                            <input type="text" 
                                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                   x-model="item.instructions" 
                                                   :name="`checklist_items[${index}][instructions]`" />
                                        </div>

                                        <div class="md:col-span-2 flex space-x-6">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" 
                                                       x-model="item.requires_photo"
                                                       :name="`checklist_items[${index}][requires_photo]`"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                       value="1">
                                                <span class="ml-2 text-sm text-gray-600">{{ __('Requires Photo') }}</span>
                                            </label>

                                            <label class="inline-flex items-center">
                                                <input type="checkbox" 
                                                       x-model="item.is_required"
                                                       :name="`checklist_items[${index}][is_required]`"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                       value="1">
                                                <span class="ml-2 text-sm text-gray-600">{{ __('Required') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="items.length === 0" class="text-gray-500 text-center py-4 bg-gray-50 rounded-lg">
                                {{ __('No checklist items added yet. Click "Add Item" to start building your template.') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>
                            {{ __('Update Template') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function templateForm() {
            return {
                items: @json($template->checklistItems),
                addItem() {
                    this.items.push({
                        id: null,
                        description: '',
                        instructions: '',
                        requires_photo: false,
                        is_required: true
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>