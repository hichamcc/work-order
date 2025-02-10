{{-- resources/views/admin/work-orders/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Work Order') }}
            </h2>
            <a href="{{ route('admin.work-orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Work Orders') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <form method="POST" action="{{ route('admin.work-orders.store') }}" class="p-6" x-data="workOrderForm()">
                    @csrf

                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" 
                                    :value="old('title')" required autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="3" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="assigned_to" :value="__('Assign To')" />
                                <select id="assigned_to" name="assigned_to" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Worker</option>
                                    @foreach($workers as $worker)
                                        <option value="{{ $worker->id }}" {{ old('assigned_to') == $worker->id ? 'selected' : '' }}>
                                            {{ $worker->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="priority" :value="__('Priority')" />
                                <select id="priority" name="priority" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="due_date" :value="__('Due Date')" />
                                <x-text-input type="date" id="due_date" name="due_date" class="mt-1 block w-full"
                                    :value="old('due_date')" />
                                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Service Template Selection -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Service Template') }}</h3>
                            
                            <div x-show="!selectedTemplate">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($templates as $template)
                                        <div class="border rounded-lg p-4 cursor-pointer hover:border-indigo-500"
                                             :class="{ 'border-indigo-500 ring-2 ring-indigo-500': selectedTemplateId === '{{ $template->id }}' }"
                                             @click="selectTemplate('{{ $template->id }}')">
                                            <div class="font-medium text-gray-900">{{ $template->name }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($template->description, 100) }}</div>
                                            <div class="mt-2 text-sm text-indigo-600">
                                                {{ $template->checklistItems->count() }} checklist items
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <input type="hidden" name="service_template_id" x-model="selectedTemplateId">
                            </div>

                            <div x-show="selectedTemplate" class="space-y-4">
                                <div class="flex justify-between items-start bg-gray-50 p-4 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900" x-text="selectedTemplate.name"></h4>
                                        <p class="text-sm text-gray-500" x-text="selectedTemplate.description"></p>
                                    </div>
                                    <button type="button" @click="clearTemplate" 
                                        class="text-sm text-red-600 hover:text-red-800">
                                        Change Template
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="item in selectedTemplate.checklist_items" :key="item.id">
                                        <div class="flex items-start space-x-3 bg-gray-50 p-3 rounded">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900" x-text="item.description"></div>
                                                <div class="mt-1 flex space-x-4">
                                                    <span x-show="item.requires_photo" class="inline-flex items-center text-xs text-blue-600">
                                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        Photo Required
                                                    </span>
                                                    <span x-show="item.is_required" class="inline-flex items-center text-xs text-yellow-600">
                                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                        </svg>
                                                        Required
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>
                            {{ __('Create Work Order') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function workOrderForm() {
            return {
                selectedTemplateId: '{{ old('service_template_id') }}',
                selectedTemplate: null,
                templates: @json($templates->load('checklistItems')),

                selectTemplate(id) {
                    this.selectedTemplateId = id;
                    this.selectedTemplate = this.templates.find(t => t.id == id);
                },

                clearTemplate() {
                    this.selectedTemplateId = null;
                    this.selectedTemplate = null;
                },

                init() {
                    if (this.selectedTemplateId) {
                        this.selectTemplate(this.selectedTemplateId);
                    }
                },
                validateForm() {
            this.errors = {};
            let isValid = true;

            // Title validation
            if (!this.$refs.title.value) {
                this.errors.title = 'A work order title is required.';
                isValid = false;
            } else if (this.$refs.title.value.length < 3) {
                this.errors.title = 'The title must be at least 3 characters.';
                isValid = false;
            }

            // Description validation
            if (!this.$refs.description.value) {
                this.errors.description = 'Please provide a description of the work order.';
                isValid = false;
            } else if (this.$refs.description.value.length < 10) {
                this.errors.description = 'The description must be at least 10 characters.';
                isValid = false;
            }

            // Worker assignment validation
            if (!this.$refs.assigned_to.value) {
                this.errors.assigned_to = 'Please select a worker to assign this work order to.';
                isValid = false;
            }

            // Due date validation
            if (this.$refs.due_date.value) {
                const selectedDate = new Date(this.$refs.due_date.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    this.errors.due_date = 'The due date must be today or a future date.';
                    isValid = false;
                }
            }

            return isValid;
        },

        submitForm() {
            if (this.validateForm()) {
                this.$refs.form.submit();
            }
        }



            }
        }
        
    </script>
    @endpush
</x-app-layout>