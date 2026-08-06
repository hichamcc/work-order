{{-- resources/views/admin/trucks/oil-history.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Oil Service History') }}
            </h2>
            <a href="{{ route('admin.trucks.service-due') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Trucks Needing Service') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                        <div>
                            <x-input-label for="truck_number" :value="__('Truck NR')" />
                            <select id="truck_number" name="truck_number"
                                class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('All trucks') }}</option>
                                @foreach($truckNumbers as $number)
                                    <option value="{{ $number }}" {{ request('truck_number') === $number ? 'selected' : '' }}>
                                        {{ $number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="date_from" :value="__('From')" />
                            <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block"
                                :value="request('date_from')" />
                        </div>
                        <div>
                            <x-input-label for="date_to" :value="__('To')" />
                            <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block"
                                :value="request('date_to')" />
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        @if(request()->hasAny(['truck_number', 'date_from', 'date_to']))
                            <a href="{{ route('admin.trucks.oil-history') }}" class="text-sm text-gray-600 hover:text-gray-900 pb-2">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Truck NR') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('KM') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Source') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Work Order') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Recorded By') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($services as $service)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $service->truck_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {{ number_format($service->km) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                                            {{ $service->km_source ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $service->serviced_at?->format('d.m.Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($service->workOrder)
                                                <a href="{{ route('admin.work-orders.show', $service->workOrder) }}"
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    #{{ $service->workOrder->id }} {{ Str::limit($service->workOrder->title, 30) }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $service->recordedBy->name ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No oil services recorded yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $services->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
