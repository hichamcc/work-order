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
                                <h3 class="text-lg font-medium text-gray-900">Customer</h3>
                                <p class="text-gray-600">{{ ($workOrder->customer ? $workOrder->customer->name : 'N/A') }}</p>
                            </div>

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
                                                                <time datetime="{{ $workOrder->completed_at }}">{{ $workOrder->completed_at->inApplicationTimezone()->format('M d, Y H:i') }}</time>
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
                                                                <time datetime="{{ $workOrder->started_at }}">{{ $workOrder->started_at->inApplicationTimezone()->format('M d, Y H:i') }}</time>
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
                                                            <time datetime="{{ $workOrder->created_at }}">{{ $workOrder->created_at->inApplicationTimezone()->format('M d, Y H:i') }}</time>
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

                    @php
                    // Pre-calculate serial number groupings for all tracked parts
                    $serialGroupings = [];
                    
                    foreach($workOrder->parts->where('part.track_serials', true)->groupBy('part_id') as $partId => $workOrderParts) {
                        $allInstances = \App\Models\PartInstance::where('part_id', $partId)
                            ->where('work_order_id', $workOrder->id)
                            ->orderBy('id')
                            ->get();
                        
                        $usedInstances = 0;
                        foreach($workOrderParts as $workOrderPart) {
                            $serialGroupings[$workOrderPart->id] = $allInstances->slice($usedInstances, $workOrderPart->quantity);
                            $usedInstances += $workOrderPart->quantity;
                        }
                    }
                @endphp
                
                <!-- Parts Used -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Parts Used</h3>
                        
                        @if($workOrder->status === 'completed')
                            <!-- Add Part Form for Completed Work Orders -->
                            <form action="{{ route('admin.work-orders.add-part', $workOrder) }}" method="POST" class="mb-6" id="addPartForm">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="col-span-2">
                                        <select name="part_id" id="part_id" required class="w-full rounded-md border-gray-300" onchange="checkSerialTracking()">
                                            <option value="">Select Part</option>
                                            @foreach(\App\Models\Part::where('is_active', true)->get() as $part)
                                                <option value="{{ $part->id }}" 
                                                        data-serialized="{{ $part->track_serials ? 'true' : 'false' }}"
                                                        data-stock="{{ $part->stock }}">
                                                    {{ $part->name }} ({{ $part->stock }} in stock)
                                                    @if($part->track_serials) [Serial Tracked] @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <input type="number" 
                                            id="quantity"
                                            name="quantity" 
                                            min="1" 
                                            value="1" 
                                            required 
                                            class="w-full rounded-md border-gray-300"
                                            placeholder="Quantity"
                                            onchange="updateSerialSelection()">
                                    </div>
                                    <div>
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            Add Part
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Serial Number Selection Section (hidden by default) -->
                                <div id="serialSelectionSection" class="mt-4 p-4 border border-gray-200 rounded-md hidden">
                                    <h4 class="font-medium mb-2">Select Serial Numbers</h4>
                                    <p class="text-sm text-gray-600 mb-3">Please select <span id="requiredCount">1</span> serial number(s):</p>
                                    
                                    <div id="serialNumbersList" class="grid grid-cols-1 md:grid-cols-3 gap-2 max-h-64 overflow-y-auto">
                                        <!-- Serial numbers will be loaded here via AJAX -->
                                        <div class="text-gray-500 italic">Loading available serial numbers...</div>
                                    </div>
                                </div>
                            </form>
                        @endif

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
                                                            $instances = $serialGroupings[$part->id] ?? collect();
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
                                            {{ $workOrder->created_at->inApplicationTimezone()->format('M d, Y H:i') }}
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
                                                <div class="flex items-center space-x-2">
                                                    <span>Started: {{ $time->started_at->inApplicationTimezone()->format('M d, Y H:i') }}</span>
                                                    @if($time->ended_at)
                                                        <span>•</span>
                                                        <span>Ended: {{ $time->ended_at->inApplicationTimezone()->format('M d, Y H:i') }}</span>
                                                    @else
                                                        <span>•</span>
                                                        <span class="text-green-600 font-medium">Ongoing</span>
                                                    @endif
                                                </div>
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

    @if($workOrder->status === 'completed')
        <!-- JavaScript for Part Adding (only for completed work orders) -->
        <script>
            // Track if the part is serialized
            let isSerialTracked = false;
            let availableStock = 0;
            
            function checkSerialTracking() {
                const partSelect = document.getElementById('part_id');
                const selectedOption = partSelect.options[partSelect.selectedIndex];
                const serialSection = document.getElementById('serialSelectionSection');
                
                isSerialTracked = selectedOption.getAttribute('data-serialized') === 'true';
                availableStock = parseInt(selectedOption.getAttribute('data-stock') || 0);
                
                // Show/hide serial number selection section
                if (isSerialTracked && partSelect.value !== '') {
                    serialSection.classList.remove('hidden');
                    loadSerialNumbers(partSelect.value);
                    updateSerialSelection();
                } else {
                    serialSection.classList.add('hidden');
                }
                
                // Update quantity max value for non-serialized parts
                const quantityInput = document.getElementById('quantity');
                if (!isSerialTracked) {
                    quantityInput.max = availableStock;
                } else {
                    quantityInput.removeAttribute('max');
                }
            }
            
            function updateSerialSelection() {
                const quantity = parseInt(document.getElementById('quantity').value);
                document.getElementById('requiredCount').textContent = quantity;
                
                // If serialized, reload serial numbers when quantity changes
                if (isSerialTracked) {
                    const partId = document.getElementById('part_id').value;
                    if (partId) {
                        loadSerialNumbers(partId);
                    }
                }
            }
            
            function loadSerialNumbers(partId) {
                const serialsList = document.getElementById('serialNumbersList');
                const quantity = parseInt(document.getElementById('quantity').value);
                
                // Show loading message
                serialsList.innerHTML = '<div class="text-gray-500 italic col-span-3">Loading available serial numbers...</div>';
                
                // Fetch available serial numbers
                fetch(`/api/parts/${partId}/serials`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            serialsList.innerHTML = '<div class="text-red-500 italic col-span-3">No serial numbers available for this part</div>';
                            return;
                        }
                        
                        let html = '';
                        data.forEach(serial => {
                            html += `
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" 
                                    name="serial_numbers[]" 
                                    value="${serial.id}" 
                                    id="serial_${serial.id}" 
                                    class="serial-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="serial_${serial.id}" class="text-sm">${serial.serial_number}</label>
                            </div>`;
                        });
                        
                        serialsList.innerHTML = html;
                        
                        // Set up event listeners for checkboxes
                        const checkboxes = document.querySelectorAll('.serial-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                enforceSelectionLimit(quantity);
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Error loading serial numbers:', error);
                        serialsList.innerHTML = '<div class="text-red-500 italic col-span-3">Error loading serial numbers</div>';
                    });
            }
            
            function enforceSelectionLimit(limit) {
                const checkboxes = document.querySelectorAll('.serial-checkbox:checked');
                
                if (checkboxes.length > limit) {
                    // If more than the limit are checked, uncheck the last one
                    checkboxes[checkboxes.length - 1].checked = false;
                }
                
                // Update the count display
                const selected = document.querySelectorAll('.serial-checkbox:checked').length;
                document.getElementById('requiredCount').textContent = `${limit} (${selected} selected)`;
            }
            
            // Form validation before submit
            document.getElementById('addPartForm').addEventListener('submit', function(e) {
                const partSelect = document.getElementById('part_id');
                
                if (partSelect.value === '') {
                    alert('Please select a part');
                    e.preventDefault();
                    return;
                }
                
                // Validate serial selection if needed
                if (isSerialTracked) {
                    const quantity = parseInt(document.getElementById('quantity').value);
                    const selectedSerials = document.querySelectorAll('.serial-checkbox:checked').length;
                    
                    if (selectedSerials !== quantity) {
                        alert(`Please select exactly ${quantity} serial numbers`);
                        e.preventDefault();
                        return;
                    }
                }
            });
            
            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                checkSerialTracking();
            });
        </script>
    @endif
</x-app-layout>