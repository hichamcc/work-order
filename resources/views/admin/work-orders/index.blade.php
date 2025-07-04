{{-- resources/views/admin/work-orders/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Work Orders') }}
            </h2>
            <a href="{{ route('admin.work-orders.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ __('New Work Order') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-10xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                <form action="{{ route('admin.work-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" 
                            :value="request('search')" placeholder="Work order title..." />
                    </div>
                    
                    <div>
                        <x-input-label for="worker" :value="__('Worker')" />
                        <select name="worker" id="worker" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Workers</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ request('worker') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
            
                    <div>
                        <x-input-label for="customer" :value="__('Customer')" />
                        <select name="customer" id="customer" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                    @if($customer->is_default)
                                        (Default)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
            
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
            
                    <div class="flex items-end space-x-2">
                        <x-primary-button type="submit">
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('admin.work-orders.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoiced</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($workOrders as $workOrder)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $workOrder->title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($workOrder->description, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $workOrder->customer ? $workOrder->customer->name : ''   }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $workOrder->assignedTo->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $workOrder->assignedTo->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="relative pt-1">
                                            <div class="overflow-hidden h-2 text-xs flex rounded bg-indigo-200">
                                                @php
                                                    $progress = $workOrder->checklist_items_count > 0 
                                                        ? ($workOrder->completed_items_count / $workOrder->checklist_items_count) * 100 
                                                        : 0;
                                                        
                                                @endphp
                                                <div style="width: {{ $progress }}%" 
                                                     class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500">
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">

                                                {{ $workOrder->completed_items_count }}/{{ $workOrder->checklist_items_count }} items
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $workOrder->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($workOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                               ($workOrder->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-gray-900">
                                                @php
                                                    $totalMinutes = $workOrder->times->sum(function($time) {
                                                        $end = $time->ended_at ?? now();
                                                        return $time->started_at->diffInMinutes($end);
                                                    });
                                                    $hours = floor($totalMinutes / 60);
                                                    $minutes = $totalMinutes % 60;
                                                    
                                                    // Get earliest started_at and latest ended_at
                                                    $firstTime = $workOrder->times->sortBy('started_at')->first();
                                                    $lastTime = $workOrder->times->sortByDesc('ended_at')->first();
                                                    
                                                    $startDateTime = $firstTime ? $firstTime->started_at->inApplicationTimezone()->format('M d, Y g:i A') : 'N/A';
                                                    $endDateTime = ($lastTime && $lastTime->ended_at) 
                                                        ? $lastTime->ended_at->inApplicationTimezone()->format('M d, Y g:i A') 
                                                        : 'Ongoing';
                                                @endphp
                                                <div class="font-medium">{{ $hours }}h {{ $minutes }}m</div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <div><span class="text-gray-700 font-medium">From:</span> {{ $startDateTime }}</div>
                                                    <div><span class="text-gray-700 font-medium">To:</span> {{ $endDateTime }}</div>
                                                </div>
                                            </span>
                                            @if($workOrder->times->count() > 0)
                                                <a href="{{ route('admin.work-orders.times.edit', $workOrder) }}" 
                                                   class="text-gray-500 hover:text-indigo-600 transition-colors duration-200" 
                                                   title="Edit time tracking">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('admin.work-orders.toggle-invoice', $workOrder) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="flex items-center cursor-pointer">
                                                <span class="relative">
                                                    <input type="checkbox" class="sr-only peer" {{ $workOrder->invoiced ? 'checked' : '' }} readonly>
                                                    <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                                </span>
                                                <span class="ml-2 text-sm {{ $workOrder->invoiced ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                                                    {{ $workOrder->invoiced ? 'Invoiced' : 'Not Invoiced' }}
                                                </span>
                                            </button>
                                        </form>
                                    </td>


                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $workOrder->due_date ? $workOrder->due_date->format('M d, Y') : 'No due date' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('admin.work-orders.show', $workOrder) }}" 
                                               class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded-md">
                                                View
                                            </a>

                                            <a href="{{ route('admin.work-orders.edit', $workOrder) }}" 
                                               class="text-orange-600 hover:text-orange-900 bg-orange-100 hover:bg-orange-200 px-3 py-1 rounded-md">
                                                Edit
                                            </a>

                                            @if($workOrder->status === 'new')
                                                <form action="{{ route('admin.work-orders.destroy', $workOrder) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this work order?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No work orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($workOrders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $workOrders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>