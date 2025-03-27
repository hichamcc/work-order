{{-- resources/views/admin/reports/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Work Order Analytics
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date Range</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filters</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <select name="worker" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">All Workers</option>
                                        @foreach($workers as $w)
                                            <option value="{{ $w->id }}" @selected($worker == $w->id)>
                                                {{ $w->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $s)
                                            <option value="{{ $s }}" @selected($status == $s)>
                                                {{ ucfirst($s) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-end justify-between">
                            <div class="flex-grow mr-2">
                                <select name="service_template" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">All Services</option>
                                    @foreach($serviceTemplates as $st)
                                        <option value="{{ $st->id }}" @selected($serviceTemplate == $st->id)>
                                            {{ $st->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    Filter
                                </button>
                                <a href="{{ route('admin.reports.index') }}" 
                                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dt class="text-sm font-medium text-gray-500">Total Orders</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $summary['total_orders'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dt class="text-sm font-medium text-gray-500">Completed</dt>
                        <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $summary['completed_orders'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dt class="text-sm font-medium text-gray-500">In Progress</dt>
                        <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $summary['in_progress_orders'] }}</dd>
                    </div>
                </div>
            </div>

       <!-- Worker Performance -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Worker Performance</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($workerPerformance as $worker)
                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center mb-4">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-700 font-medium">{{ substr($worker->name, 0, 2) }}</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-lg font-medium text-gray-900">{{ $worker->name }}</h4>
                            <span class="text-sm text-gray-500">
                                {{ $worker->total_orders ? round(($worker->completed_orders / $worker->total_orders) * 100) : 0 }}% completion rate
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Orders</div>
                            <div class="mt-1 text-xl font-semibold text-gray-900">{{ $worker->total_orders }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Completed</div>
                            <div class="mt-1 text-xl font-semibold text-green-600">{{ $worker->completed_orders }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                       <!-- Average Time -->
                        <div>
                            <div class="text-sm font-medium text-gray-500">Average Time</div>
                            <div class="mt-1 text-xl font-semibold text-indigo-600">
                                @php
                                    $hours = floor($worker->total_time / $worker->total_orders / 60);
                                    $minutes = round($worker->total_time / $worker->total_orders % 60);
                                @endphp
                                {{ $hours }}h {{ $minutes }}m
                            </div>
                        </div>

                        <!-- Total Time -->
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Time</div>
                            <div class="mt-1 text-xl font-semibold text-indigo-600">
                                @php
                                    $hours = floor($worker->total_time / 60);
                                    $minutes = round($worker->total_time % 60);
                                @endphp
                                {{ $hours }}h {{ $minutes }}m
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Service Analysis -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Service Analysis</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($serviceDistribution as $service)
                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center mb-4">
                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-lg font-medium text-gray-900">{{ $service->serviceTemplate ? $service->serviceTemplate->name : 'Without templates'}}  </h4>
                            <span class="text-sm text-gray-500">
                                {{ $service->total ? round(($service->completed / $service->total) * 100) : 0 }}% completion rate
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Orders</div>
                            <div class="mt-1 text-xl font-semibold text-gray-900">{{ $service->total }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Completed</div>
                            <div class="mt-1 text-xl font-semibold text-green-600">{{ $service->completed }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">

                        <div >
                            <div class="text-sm font-medium text-gray-500">Average Time</div>
                            <div class="mt-1 text-xl font-semibold text-indigo-600">
                                @php
                                    $hours = floor($service->total_time / $service->total  / 60);
                                    $minutes = round($service->total_time  / $service->total % 60);
                                @endphp
                                {{ $hours }}h {{ $minutes }}m
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Time</div>
                            <div class="mt-1 text-xl font-semibold text-indigo-600">
                                @php
                                    $hours = floor($service->total_time / 60);
                                    $minutes = round($service->total_time % 60);
                                @endphp
                                {{ $hours }}h {{ $minutes }}m
                            </div>
                        </div>
                    </div>
                    <div class="col-span-2 mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" 
                                     style="width: {{ $service->total ? ($service->completed / $service->total) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
        </div>
    </div>
</x-app-layout>