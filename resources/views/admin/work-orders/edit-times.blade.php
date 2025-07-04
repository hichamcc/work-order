{{-- resources/views/admin/work-orders/edit-times.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Time Tracking') }} - {{ $workOrder->title }}
            </h2>
            <a href="{{ route('admin.work-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Work Orders') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Work Order Info -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Work Order Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Title:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $workOrder->title }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Assigned To:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $workOrder->assignedTo->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $workOrder->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($workOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                   ($workOrder->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 
                                   'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Time Entries -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Time Entries</h3>
                    <p class="mt-1 text-sm text-gray-600">Edit or delete time tracking entries for this work order.</p>
                </div>

                <div class="overflow-hidden">
                    @if($workOrder->times->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($workOrder->times as $time)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $time->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $time->user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form action="{{ route('admin.work-orders.times.update', [$workOrder, $time]) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="datetime-local" 
                                                           name="started_at" 
                                                           value="{{ $time->started_at->format('Y-m-d\TH:i') }}"
                                                           class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                           required>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="datetime-local" 
                                                       name="ended_at" 
                                                       value="{{ $time->ended_at ? $time->ended_at->format('Y-m-d\TH:i') : '' }}"
                                                       class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $duration = $time->ended_at ? $time->started_at->diff($time->ended_at) : $time->started_at->diff(now());
                                                    $totalMinutes = $duration->days * 24 * 60 + $duration->h * 60 + $duration->i;
                                                    $hours = floor($totalMinutes / 60);
                                                    $minutes = $totalMinutes % 60;
                                                @endphp
                                                <div class="text-sm text-gray-900">
                                                    {{ $hours }}h {{ $minutes }}m
                                                    @if(!$time->ended_at)
                                                        <span class="text-xs text-blue-600">(ongoing)</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <textarea name="notes" 
                                                          rows="2" 
                                                          class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                          placeholder="Add notes...">{{ $time->notes }}</textarea>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Save
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.work-orders.times.destroy', [$workOrder, $time]) }}" 
                                                      method="POST" 
                                                      class="inline-block"
                                                      onsubmit="return confirm('Are you sure you want to delete this time entry?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries</h3>
                                <p class="mt-1 text-sm text-gray-500">This work order has no time tracking entries yet.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary -->
            @if($workOrder->times->count() > 0)
                <div class="mt-6 bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Summary</h3>
                    </div>
                    <div class="p-6">
                        @php
                            $totalMinutes = $workOrder->times->sum(function($time) {
                                $end = $time->ended_at ?? now();
                                return $time->started_at->diffInMinutes($end);
                            });
                            $totalHours = floor($totalMinutes / 60);
                            $totalMinutesRemainder = $totalMinutes % 60;
                            
                            $firstTime = $workOrder->times->sortBy('started_at')->first();
                            $lastTime = $workOrder->times->sortByDesc('ended_at')->first();
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Total Time:</span>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $totalHours }}h {{ $totalMinutesRemainder }}m</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">First Entry:</span>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $firstTime ? $firstTime->started_at->format('M d, Y g:i A') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Last Entry:</span>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ ($lastTime && $lastTime->ended_at) ? $lastTime->ended_at->format('M d, Y g:i A') : 'Ongoing' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>