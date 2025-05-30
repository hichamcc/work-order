{{-- resources/views/worker/work-orders/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Work Orders') }}
        </h2>
        <a href="{{ route('worker.work-orders.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ __('Add Work Order') }}
        </a>
    </div>
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
                                                    @if($workOrder->due_date->isPast() && $workOrder->status !== 'completed' )
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
                                    @if($workOrder->status !== 'completed')
                                        <div class="flex items-center space-x-4">
                                            @if(!$activeTimings->contains($workOrder->id))
                                                <a href="{{ route('worker.work-orders.time-tracking', $workOrder) }}" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                    </svg>
                                                    Start Work
                                                </a>
                                            @else
                                                <a href="{{ route('worker.work-orders.time-tracking', $workOrder) }}" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd"/>
                                                    </svg>
                                                    In Progress...
                                                </a>
                                            @endif

                                                 <!-- Mark as Completed Button -->
                                                 <button onclick="markAsCompleted('{{ $workOrder->id }}')" 
                                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Complete
                                            </button>
                                        </div>
                                    @endif
                                        <a href="{{ route('worker.work-orders.show', $workOrder) }}" 
                                           class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                            Details & progress
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


      <!-- Completion Confirmation Modal -->
      <div id="completionConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Mark Work Order as Completed?</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to mark this work order as completed? This action cannot be undone.
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex justify-center space-x-4">
                    <button id="confirmComplete" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                        Yes, Complete It
                    </button>
                    <button id="cancelComplete" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-auto shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for completing work orders -->
    <form id="completeWorkForm" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="completed">
    </form>


    @push('scripts')
    <script>
        let currentWorkOrderId = null;
        
        function markAsCompleted(workOrderId) {
            currentWorkOrderId = workOrderId;
            document.getElementById('completionConfirmModal').classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const confirmModal = document.getElementById('completionConfirmModal');
            const confirmBtn = document.getElementById('confirmComplete');
            const cancelBtn = document.getElementById('cancelComplete');
            const completeForm = document.getElementById('completeWorkForm');

            // Handle confirm completion
            confirmBtn.addEventListener('click', function() {
                if (currentWorkOrderId) {
                    // Update form action with the work order ID
                    completeForm.action = `/worker/${currentWorkOrderId}/update-status`;
                    
                    // Submit the form
                    completeForm.submit();
                }
            });

            // Handle cancel
            cancelBtn.addEventListener('click', function() {
                confirmModal.classList.add('hidden');
                currentWorkOrderId = null;
            });

            // Close modal when clicking outside
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) {
                    confirmModal.classList.add('hidden');
                    currentWorkOrderId = null;
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !confirmModal.classList.contains('hidden')) {
                    confirmModal.classList.add('hidden');
                    currentWorkOrderId = null;
                }
            });
        });
    </script>
@endpush

</x-app-layout>
