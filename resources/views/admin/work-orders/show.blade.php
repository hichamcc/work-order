{{-- resources/views/admin/work-orders/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $workOrder->title }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.work-orders.edit', $workOrder) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit Work Order') }}
                </a>
                <a href="{{ route('admin.work-orders.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Work Order Details -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="prose max-w-none">
                                <h3 class="text-lg font-medium text-gray-900">Description</h3>
                                <p class="text-gray-600">{{ $workOrder->description }}</p>
                            </div>

                            <!-- Timeline of Status Changes -->
                            <div class="mt-6">
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        @if($workOrder->completed_at)
                                            <li>
                                                <div class="relative pb-8">
                                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                            <div>
                                                                <p class="text-sm text-gray-500">Completed</p>
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                <time datetime="{{ $workOrder->completed_at }}">{{ $workOrder->completed_at->format('M d, Y H:i') }}</time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        @if($workOrder->started_at)
                                            <li>
                                                <div class="relative pb-8">
                                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                            <div>
                                                                <p class="text-sm text-gray-500">Started Work</p>
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                <time datetime="{{ $workOrder->started_at }}">{{ $workOrder->started_at->format('M d, Y H:i') }}</time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        <li>
                                            <div class="relative pb-8">
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h.01M15 10h.01M9 16h.01M15 16h.01M9 13h.01M15 13h.01"></path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                        <div>
                                                            <p class="text-sm text-gray-500">Created</p>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <time datetime="{{ $workOrder->created_at }}">{{ $workOrder->created_at->format('M d, Y H:i') }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Checklist Items</h3>
                            <div class="space-y-4">
                                @foreach($workOrder->checklistItems as $item)
                                    @include('admin.work-orders._partials._checklist-item', ['item' => $item])
                                @endforeach
                            </div>
                        </div>
                    </div>

              <!-- Parts Used -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Parts Used</h3>
                        @if($workOrder->parts->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Part</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Serial Numbers</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($workOrder->parts as $part)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $part->part->name }}
                                                    <div class="text-xs text-gray-500">{{ $part->part->part_number }}</div>
                                                    @if($part->part->track_serials)
                                                        <span class="px-2 mt-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                            Serial Tracked
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    @if($part->part->track_serials)
                                                        @php
                                                            $instances = \App\Models\PartInstance::where('part_id', $part->part_id)
                                                                ->where('work_order_id', $workOrder->id)
                                                                ->get();
                                                        @endphp
                                                        
                                                        @if($instances->count() > 0)
                                                            <div class="space-y-1">
                                                                @foreach($instances as $instance)
                                                                    <div class="flex items-center">
                                                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $instance->serial_number }}</span>
                                                                        @if($instance->status === 'assigned')
                                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                                                Assigned
                                                                            </span>
                                                                        @elseif($instance->status === 'used')
                                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                                Used
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-gray-500 italic">No serial numbers assigned</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-500">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $part->quantity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    DKR {{ number_format($part->cost_at_time * $part->quantity, 2) }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $part->notes }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900">Total</td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                DKR {{ number_format($workOrder->parts->sum(function($part) {
                                                    return $part->cost_at_time * $part->quantity;
                                                }), 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No parts have been used yet.</p>
                        @endif
                    </div>
                </div>

                    <!-- Comments -->
                    @include('admin.work-orders._partials._comments', ['comments' => $workOrder->comments])
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Status Information</h3>
                            <dl class="divide-y divide-gray-200">
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        @include('admin.work-orders._partials._status-badge', ['status' => $workOrder->status])
                                    </dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $workOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : 
                                               ($workOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                               ($workOrder->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-green-100 text-green-800')) }}">
                                            {{ ucfirst($workOrder->priority) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $workOrder->due_date ? $workOrder->due_date->format('M d, Y') : 'No due date set' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Assignment Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment</h3>
                            <dl class="divide-y divide-gray-200">
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $workOrder->assignedTo->name }}
                                    </dd>
                                </div>
                                @if($workOrder->helpers->isNotEmpty())
                                    <dt class="text-sm font-medium text-gray-500 mt-4">Other Workers</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach($workOrder->helpers as $helper)
                                                <li>{{ $helper->name }}</li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                @endif

                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $workOrder->createdBy->name }}
                                        <div class="text-xs text-gray-500">
                                            {{ $workOrder->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Time Tracking -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Time Tracking</h3>
                            @if($workOrder->times->count() > 0)
                                <div class="space-y-4">
                                    @foreach($workOrder->times as $time)
                                        <div class="border-b pb-4 last:border-0 last:pb-0">
                                            <div class="flex justify-between">
                                                <div class="text-sm text-gray-900">{{ $time->user->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    @if($time->ended_at)
                                                        {{ $time->started_at->diffForHumans($time->ended_at, \Carbon\CarbonInterface::DIFF_ABSOLUTE, ['parts' => 3]) }}
                                                    @else
                                                        In Progress
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $time->started_at->format('M d, Y H:i') }}
                                               
                                            </div>
                                            @if($time->notes)
                                                <div class="text-sm text-gray-600 mt-2">
                                                    {{ $time->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 pt-4 border-t">
                                    <div class="flex justify-between text-sm font-medium">
                                        <span class="text-gray-500">Total Time:</span>
                                        <span class="text-gray-900">
                                            @php
                                                $totalMinutes = $workOrder->times->sum(function($time) {
                                                    $end = $time->ended_at ?? now();
                                                    return $time->started_at->diffInMinutes($end);
                                                });
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            {{ $hours }}h {{ $minutes }}m
                                        </span>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No time entries recorded yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>