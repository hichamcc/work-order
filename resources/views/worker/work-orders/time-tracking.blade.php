<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Time Tracking - Work Order #{{ $workOrder->title }}
            </h2>
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
            <!-- Work Order Basic Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Work Order Details</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Service Type</dt>
                            {{ $workOrder->serviceTemplate ? $workOrder->serviceTemplate->name : 'No template assigned' }}
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Time Tracker Component -->
            <x-work-order.time-tracker 
                :workOrder="$workOrder" 
                :activeTiming="$activeTiming" 
            />

            <!-- Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <a href="{{ route('worker.work-orders.show', $workOrder) }}" 
                       class="text-indigo-600 hover:text-indigo-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Work Order Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timerElement = document.getElementById('timer');
            if (timerElement) {
                const startTime = parseInt(timerElement.dataset.startTime);
                
                function updateTimer() {
                    const now = Math.floor(Date.now() / 1000);
                    const diffInSeconds = now - startTime;
                    
                    const hours = Math.floor(diffInSeconds / 3600);
                    const minutes = Math.floor((diffInSeconds % 3600) / 60);
                    const seconds = diffInSeconds % 60;
                    
                    timerElement.textContent = 
                        String(hours).padStart(2, '0') + ':' +
                        String(minutes).padStart(2, '0') + ':' +
                        String(seconds).padStart(2, '0');
                }

                // Update immediately and then every second
                updateTimer();
                setInterval(updateTimer, 1000);
            }
        });
    </script>
    @endpush
</x-app-layout>