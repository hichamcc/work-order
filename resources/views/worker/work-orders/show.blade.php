<!-- resources/views/worker/work-orders/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Work Order Details
            </h2>
            <!-- Status Badge -->
            <span class="px-4 py-2 rounded-full text-sm font-semibold 
                @if($workOrder->status === 'new') bg-blue-100 text-blue-800
                @elseif($workOrder->status === 'in_progress') bg-yellow-100 text-yellow-800
                @elseif($workOrder->status === 'on_hold') bg-red-100 text-red-800
                @elseif($workOrder->status === 'completed') bg-green-100 text-green-800
                @endif">
                {{ ucfirst($workOrder->status) }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Work Order Info -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Work Order Information</h3>
                            <dl class="grid grid-cols-1 gap-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Service Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->serviceTemplate->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->description }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $workOrder->due_date ? $workOrder->due_date->format('M d, Y H:i') : 'No due date set' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Time Tracking -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Time Tracking</h3>
                            @if($workOrder->status !== 'completed')
                                <div class="mb-4">
                                    @if(!$activeTiming)
                                        <form action="{{ route('worker.work-orders.start-work', $workOrder) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                Start Work
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('worker.work-orders.pause-work', $workOrder) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                                Pause Work
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                           <!-- Time Entries -->
                            <div class="space-y-2">
                            @foreach($workOrder->times as $time)
                                <div class="text-sm bg-gray-50 p-2 rounded">
                                    <div class="flex justify-between">
                                        <span>{{ $time->started_at->format('M d, Y H:i') }}</span>
                                        <span>
                                            @if($time->ended_at)
                                                {{ $time->ended_at->format('H:i') }}
                                                @php
                                                    $diffInSeconds = $time->started_at->diffInSeconds($time->ended_at);
                                                    $hours = floor($diffInSeconds / 3600);
                                                    $minutes = floor(($diffInSeconds % 3600) / 60);
                                                    $seconds = $diffInSeconds % 60;
                                                @endphp
                                                ({{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }})
                                            @else
                                                (In Progress)
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Total Time -->
                            <div class="mt-4 text-sm font-medium text-right">
                                @php
                                    $totalSeconds = $workOrder->times->sum(function($time) {
                                        $end = $time->ended_at ?? now();
                                        return $time->started_at->diffInSeconds($end);
                                    });
                                    $totalHours = floor($totalSeconds / 3600);
                                    $totalMinutes = floor(($totalSeconds % 3600) / 60);
                                    $totalSeconds = $totalSeconds % 60;
                                @endphp
                                Total: {{ sprintf('%02d:%02d:%02d', $totalHours, $totalMinutes, $totalSeconds) }}
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Checklist</h3>
                    <div class="space-y-4">
                        @foreach($workOrder->checklistItems as $item)
                            <div class="border rounded-lg p-4">
                                <form action="{{ route('worker.work-orders.update-checklist-item', [$workOrder, $item->id]) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <div class="space-y-4">
                                        <!-- Checklist Item Header -->
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start space-x-3">
                                                <input type="checkbox" 
                                                       name="is_completed" 
                                                       value="1" 
                                                       @checked($item->is_completed)
                                                       class="mt-1 rounded border-gray-300 text-indigo-600">
                                                <div>
                                                    <label class="text-sm font-medium text-gray-700">
                                                        {{ $item->checklistItem->description }}
                                                        @if($item->checklistItem->is_required)
                                                            <span class="text-red-500">*</span>
                                                        @endif
                                                    </label>
                                                    @if($item->checklistItem->requires_photo)
                                                        <span class="ml-2 text-sm text-gray-500">(Photo Required)</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($item->completed_at)
                                                <span class="text-xs text-gray-500">
                                                    Completed {{ $item->completed_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Notes -->
                                        <div>
                                            <textarea name="notes" 
                                                      rows="2" 
                                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                      placeholder="Add notes...">{{ $item->notes }}</textarea>
                                        </div>

                                        <!-- Photo Upload -->
                                        @if($item->checklistItem->requires_photo)
                                            <div>
                                                <input type="file" 
                                                       name="photos[]" 
                                                       multiple 
                                                       accept="image/*"
                                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            </div>
                                        @endif

                                        <!-- Existing Photos -->
                                        @if($item->photos->count() > 0)
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                                @foreach($item->photos as $photo)
                                                    <div class="relative group">
                                                        <img src="{{ Storage::url($photo->file_path) }}" 
                                                             alt="Checklist item photo" 
                                                             class="w-full h-32 object-cover rounded">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Save Button -->
                                        <div>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Parts Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Parts Used</h3>
                    
                    @if($workOrder->status !== 'completed')
                        <!-- Add Part Form -->
                        <form action="{{ route('worker.work-orders.add-part', $workOrder) }}" method="POST" class="mb-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="col-span-2">
                                    <select name="part_id" required class="w-full rounded-md border-gray-300">
                                        <option value="">Select Part</option>
                                        @foreach($parts as $part)
                                            <option value="{{ $part->id }}">{{ $part->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="number" 
                                           name="quantity" 
                                           min="1" 
                                           value="1" 
                                           required 
                                           class="w-full rounded-md border-gray-300"
                                           placeholder="Quantity">
                                </div>
                                <div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Add Part
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                    <!-- Parts List -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Part</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($workOrder->parts as $part)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $part->part->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ $part->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            ${{ number_format($part->cost_at_time * $part->quantity, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                        Total Cost:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        ${{ number_format($workOrder->parts->sum(function($part) {
                                            return $part->cost_at_time * $part->quantity;
                                        }), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Comments</h3>
                    
                    @if($workOrder->status !== 'completed')
                        <!-- Add Comment Form -->
                        <form action="{{ route('worker.work-orders.add-comment', $workOrder) }}" method="POST" class="mb-6">
                            @csrf
                            <div class="space-y-4">
                                <textarea name="comment" 
                                         rows="3" 
                                         required
                                         class="w-full rounded-md border-gray-300"
                                         placeholder="Add a comment..."></textarea>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Add Comment
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- Comments List -->
                    <div class="space-y-4">
                        @foreach($workOrder->comments->sortByDesc('created_at') as $comment)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                    <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-700">{{ $comment->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

                        <!-- Status Update Section -->
                        @if($workOrder->status !== 'completed')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Update Status</h3>
                        <form action="{{ route('worker.work-orders.update-status', $workOrder) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="space-y-4">
                                <div>
                                    <select name="status" required 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="in_progress" @selected($workOrder->status === 'in_progress')>In Progress</option>
                                        <option value="on_hold" @selected($workOrder->status === 'on_hold')>On Hold</option>
                                        <option value="completed" @selected($workOrder->status === 'completed')>Completed</option>
                                    </select>
                                </div>
                                
                                <div id="holdReasonContainer" class="@if($workOrder->status !== 'on_hold') hidden @endif">
                                    <label for="hold_reason" class="block text-sm font-medium text-gray-700">Hold Reason</label>
                                    <textarea id="hold_reason"
                                              name="hold_reason" 
                                              rows="3" 
                                              class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                              placeholder="Please provide a reason for putting the work order on hold...">{{ $workOrder->hold_reason }}</textarea>
                                </div>

                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status update handling
            const statusSelect = document.querySelector('select[name="status"]');
            const holdReasonContainer = document.getElementById('holdReasonContainer');
            const holdReasonTextarea = document.querySelector('textarea[name="hold_reason"]');

            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    if (this.value === 'on_hold') {
                        holdReasonContainer.classList.remove('hidden');
                        holdReasonTextarea.required = true;
                    } else {
                        holdReasonContainer.classList.add('hidden');
                        holdReasonTextarea.required = false;
                        holdReasonTextarea.value = '';
                    }
                });
            }

            // Auto-submit checklist items on checkbox change
            document.querySelectorAll('input[name="is_completed"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });

            // File upload preview
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function() {
                    const previewContainer = this.closest('form').querySelector('.photo-preview');
                    if (previewContainer) {
                        previewContainer.innerHTML = '';
                        [...this.files].forEach(file => {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'w-20 h-20 object-cover rounded';
                                previewContainer.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        });
                    }
                });
            });
        });
    </script>
    @endpush

    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</x-app-layout> 