{{-- resources/views/admin/work-orders/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Work Order') }}
            </h2>
            <a href="{{ route('admin.work-orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Work Orders') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <!-- Status Section -->
                    <div class="mb-6 pb-6 border-b">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Current Status</h3>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        {{ $workOrder->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($workOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                           ($workOrder->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 
                                           'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                    </span>
                                    @if($workOrder->started_at)
                                        <span class="text-sm text-gray-500">
                                            Started: {{ $workOrder->started_at->format('M d, Y H:i') }}
                                        </span>
                                    @endif
                                    @if($workOrder->completed_at)
                                        <span class="text-sm text-gray-500">
                                            Completed: {{ $workOrder->completed_at->format('M d, Y H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                @unless($workOrder->status === 'completed')
                                    <form action="{{ route('admin.work-orders.update-status', $workOrder) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                            Mark as Completed
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>

                        @if($workOrder->hold_reason)
                            <div class="mt-4 bg-yellow-50 p-4 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">On Hold Reason</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>{{ $workOrder->hold_reason }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Edit Form -->
                    <form method="POST" action="{{ route('admin.work-orders.update', $workOrder) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <x-input-label for="title" :value="__('Title')" />
                                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" 
                                        :value="old('title', $workOrder->title)" required />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div class="col-span-2">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="3" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $workOrder->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="assigned_to" :value="__('Assign To')" />
                                    <select id="assigned_to" name="assigned_to" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select Worker</option>
                                        @foreach($workers as $worker)
                                            <option value="{{ $worker->id }}" 
                                                {{ old('assigned_to', $workOrder->assigned_to) == $worker->id ? 'selected' : '' }}>
                                                {{ $worker->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="helpers" :value="__('Other Workers (Optional)')" />
                                    <select id="helpers" name="helpers[]" multiple class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @foreach($workers as $worker)
                                            <option value="{{ $worker->id }}" 
                                                    {{ in_array($worker->id, old('helpers', $workOrder->helpers->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                {{ $worker->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple workers</p>
                                    <x-input-error :messages="$errors->get('helpers')" class="mt-2" />
                                </div>


                                <!-- Customer Selection -->
                                <div>
                                    <x-input-label for="customer_id" :value="__('Customer')" />
                                    <select name="customer_id" 
                                            id="customer_id" 
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            required>
                                        <option value="">Select a customer</option>
                                        @foreach(\App\Models\Customer::orderBy('is_default', 'desc')->orderBy('name')->get() as $customer)
                                            <option value="{{ $customer->id }}" 
                                                    {{ old('customer_id', $workOrder->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                                @if($customer->is_default)
                                                    (Default)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="priority" :value="__('Priority')" />
                                    <select id="priority" name="priority" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="low" {{ old('priority', $workOrder->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $workOrder->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $workOrder->priority) == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority', $workOrder->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="due_date" :value="__('Due Date')" />
                                    <x-text-input type="date" id="due_date" name="due_date" class="mt-1 block w-full"
                                        :value="old('due_date', optional($workOrder->due_date)->format('Y-m-d'))" />
                                    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Service Template Info (Read-only) -->
                            @if($workOrder->serviceTemplate)
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Service Template') }}</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="font-medium text-gray-900">{{ $workOrder->serviceTemplate->name }}</div>
                                        <div class="text-sm text-gray-500 mt-1">{{ $workOrder->serviceTemplate->description }}</div>
                                        
                                        <div class="mt-4 space-y-2">
                                            @foreach($workOrder->checklistItems as $item)
                                                <div class="flex items-start space-x-3 bg-white p-3 rounded">
                                                    <div class="flex-shrink-0">
                                                        @if($item->is_completed)
                                                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $item->checklistItem->description }}
                                                        </div>
                                                        @if($item->is_completed)
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                Completed by {{ $item->completedByUser->name }} 
                                                                on {{ $item->completed_at->format('M d, Y H:i') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                {{ __('Update Work Order') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>