{{-- resources/views/worker/work-orders/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Work Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                <form action="{{ route('worker.work-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Status</option>
                            <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="priority" :value="__('Priority')" />
                        <select name="priority" id="priority" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Priorities</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="date" :value="__('Date Range')" />
                        <select name="date" id="date" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Time</option>
                            <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date') === 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <x-primary-button type="submit">
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('worker.work-orders.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Work Orders List -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 space-y-6">
                    @forelse ($workOrders as $workOrder)
                        <div class="border rounded-lg overflow-hidden {{ $workOrder->priority === 'urgent' ? 'border-red-300' : 'border-gray-200' }}">
                            <div class="p-6 {{ $workOrder->priority === 'urgent' ? 'bg-red-50' : 'bg-white' }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $workOrder->title }}</h3>
                                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $workOrder->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($workOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                                   ($workOrder->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                            </span>
                                            @if($workOrder->priority === 'urgent')
                                                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Urgent
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <p class="mt-1 text-sm text-gray-600">{{ Str::limit($workOrder->description, 150) }}</p>
                                        
                                        <div class="mt-4 flex items-center space-x-4">
                                            @if($workOrder->serviceTemplate)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ $workOrder->serviceTemplate->name }}
                                                </span>
                                            @endif
                                            
                                            @if($workOrder->due_date)
                                                <span class="inline-flex items-center text-xs text-gray-500">
                                                    Due: {{ $workOrder->due_date->format('M d, Y') }}
                                                    @if($workOrder->due_date->isPast())
                                                        <span class="ml-1 text-red-600 font-medium">Overdue</span>
                                                    @elseif($workOrder->due_date->isToday())
                                                        <span class="ml-1 text-yellow-600 font-medium">Due Today</span>
                                                    @endif
                                                </span>
                                            @endif

                                            @if($workOrder->started_at)
                                                <span class="inline-flex items-center text-xs text-gray-500">
                                                    Started: {{ $workOrder->started_at->format('M d, Y H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Progress Bar -->
                                        @if($workOrder->checklistItems_count > 0)
                                            <div class="mt-4">
                                                <div class="relative pt-1">
                                                    <div class="flex mb-2 items-center justify-between">
                                                        <div>
                                                            <span class="text-xs font-semibold inline-block text-indigo-600">
                                                                Progress
                                                            </span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-xs font-semibold inline-block text-indigo-600">
                                                                {{ $workOrder->completed_items_count }}/{{ $workOrder->checklistItems_count }} Items
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="overflow-hidden h-2 text-xs flex rounded bg-indigo-200">
                                                        @php
                                                            $progress = ($workOrder->completed_items_count / $workOrder->checklistItems_count) * 100;
                                                        @endphp
                                                        <div style="width: {{ $progress }}%" 
                                                             class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                        @if($workOrder->status === 'new')
                                            <form action="{{ route('worker.work-orders.start', $workOrder) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                    Start Work
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('worker.work-orders.show', $workOrder) }}" 
                                           class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                            Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No work orders found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    @if($workOrders->hasPages())
                        <div class="mt-6">
                            {{ $workOrders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>