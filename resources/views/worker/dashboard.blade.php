{{-- resources/views/worker/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg col-span-1">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Active Orders</div>
                        <div class="mt-1 text-2xl font-semibold text-indigo-600">{{ $stats['active_orders'] }}</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm rounded-lg col-span-1">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">On Hold</div>
                        <div class="mt-1 text-2xl font-semibold text-yellow-600">{{ $stats['on_hold'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg col-span-1">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Urgent</div>
                        <div class="mt-1 text-2xl font-semibold text-red-600">{{ $stats['urgent_orders'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg col-span-1">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Today</div>
                        <div class="flex justify-between items-end">
                            <div class="text-2xl font-semibold text-green-600">{{ $stats['completed_today'] }}</div>
                            <div class="text-sm text-gray-500">completed</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg col-span-1">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Hours Today</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['hours_today'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg col-span-1">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Total Completed</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_completed'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Active Work -->
            @if($activeTime)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Currently Working On</h3>
                                <div class="flex items-center mt-1">
                                    <div class="flex-shrink-0">
                                        <span class="flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                        </span>
                                    </div>
                                    <p class="ml-2 text-sm text-gray-500">Started {{ $activeTime->started_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <form action="{{ route('worker.work-orders.pause', $activeTime->workOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Pause Work
                                </button>
                            </form>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('worker.work-orders.show', $activeTime->workOrder) }}" class="text-xl font-medium text-gray-900 hover:text-indigo-600">
                                {{ $activeTime->workOrder->title }}
                            </a>
                            <p class="mt-1 text-gray-600">{{ $activeTime->workOrder->description }}</p>
                            <div class="mt-4 flex space-x-4">
                                @if($activeTime->workOrder->serviceTemplate)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                        {{ $activeTime->workOrder->serviceTemplate->name }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $activeTime->workOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : 
                                       ($activeTime->workOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                       'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($activeTime->workOrder->priority) }} Priority
                                </span>
                                @if($activeTime->workOrder->due_date)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        Due {{ $activeTime->workOrder->due_date->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Work Orders -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Recent Work Orders</h3>
                        <a href="{{ route('worker.work-orders.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            View All
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($activeOrders as $order)
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <a href="{{ route('worker.work-orders.show', $order) }}" class="text-lg font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $order->title }}
                                        </a>
                                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $order->status === 'new' ? 'bg-gray-100 text-gray-800' : 
                                               ($order->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                               'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                        @if($order->priority === 'urgent')
                                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Urgent
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ Str::limit($order->description, 100) }}</p>
                                    @if($order->due_date)
                                        <p class="mt-2 text-sm text-gray-500">
                                            Due: {{ $order->due_date->format('M d, Y') }}
                                            @if($order->due_date->isPast())
                                                <span class="text-red-600 font-medium">Overdue</span>
                                            @elseif($order->due_date->isToday())
                                                <span class="text-yellow-600 font-medium">Due Today</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                        @if($order->status === 'new')
                                            <form action="{{ route('worker.work-orders.start', $order) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                    Start Work
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('worker.work-orders.show', $order) }}" 
                                        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                            Details
                                        </a>
                                    </div>

                                </div>
                            <!-- Progress Bar -->
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
                                                {{ $order->completed_items_count }}/{{ $order->checklistItems_count }} Items
                                            </span>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden h-2 text-xs flex rounded bg-indigo-200">
                                        @php
                                        $progress = $order->checklistItems_count > 0 
                                                ? ($order->completed_items_count / $order->checklistItems_count) * 100 
                                                : 0;
                                        @endphp
                                        <div style="width: {{ $progress }}%" 
                                             class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">
                            No active work orders.
                            @if($stats['completed_today'] > 0)
                                <p class="mt-1 text-sm">You've completed {{ $stats['completed_today'] }} orders today!</p>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            @if(session('success'))
                <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg" 
                     x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     x-transition>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg"
                     x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     x-transition>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>