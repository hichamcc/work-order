{{-- resources/views/components/work-order/time-tracker.blade.php --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-4 sm:p-6">
        <div class="sm:flex sm:items-center sm:justify-between">
            <h3 class="text-lg font-medium text-gray-900">Time Tracking</h3>
            
            @if($workOrder->status !== 'completed')
                <div class="mt-3 sm:mt-0">
                    @if(!$activeTiming)
                        <form action="{{ route('worker.work-orders.start-work', $workOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                                Start Work
                            </button>
                        </form>
                    @else
                        <div class="flex items-center space-x-4">
                            <div id="timer" 
                                 class="font-mono font-medium text-gray-900"
                                 data-start-time="{{ $activeTiming->started_at->timestamp }}">
                                00:00:00
                            </div>
                            <form action="{{ route('worker.work-orders.pause-work', $workOrder) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Pause Work
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Time Entries -->
        <div class="mt-6 flow-root">
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Start Time</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">End Time</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($workOrder->times->sortByDesc('started_at') as $time)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">
                                        {{ $time->started_at->format('M d, Y H:i:s') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($time->ended_at)
                                            {{ $time->ended_at->format('M d, Y H:i:s') }}
                                        @else
                                            <span class="text-yellow-600 font-medium">In Progress</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                                    @php
                                        $endTime = $time->ended_at ?? now();
                                        $diffInSeconds = $time->started_at->diffInSeconds($endTime);
                                        $hours = floor($diffInSeconds / 3600);
                                        $minutes = floor(($diffInSeconds % 3600) / 60);
                                        $seconds = $diffInSeconds % 60;
                                    @endphp
                                        {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="px-3 py-4 text-sm font-medium text-gray-900 text-right">
                                    Total Time:
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900 text-right">
                                @php
                                $totalSeconds = $workOrder->times->sum(function($time) {
                                    $end = $time->ended_at ?? now();
                                    return $time->started_at->diffInSeconds($end); // Fixed order of diff calculation
                                });
                                $totalHours = floor($totalSeconds / 3600);
                                $totalMinutes = floor(($totalSeconds % 3600) / 60);
                                $totalSeconds = $totalSeconds % 60;
                                @endphp
                                    {{ sprintf('%02d:%02d:%02d', $totalHours, $totalMinutes, $totalSeconds) }}
                                </td>

                            </tr>
                        </tfoot>
                    </table>
                </div>
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