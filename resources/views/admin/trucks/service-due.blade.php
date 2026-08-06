{{-- resources/views/admin/trucks/service-due.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trucks Needing Oil Service') }}
            </h2>
            <a href="{{ route('admin.trucks.oil-history') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Oil Service History') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="mb-4 text-sm text-gray-600">
                        {{ __('Trucks that have driven at least') }}
                        <strong>{{ number_format($intervalKm) }} km</strong>
                        {{ __('since their last recorded oil service. Updated automatically every day at 04:00.') }}
                    </div>

                    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                        <div>
                            <x-input-label for="search" :value="__('Truck NR')" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block"
                                :value="request('search')" placeholder="{{ __('Search truck') }}" />
                        </div>
                        <label class="flex items-center space-x-2 pb-2">
                            <input type="checkbox" name="show_resolved" value="1"
                                {{ request()->boolean('show_resolved') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">{{ __('Show resolved') }}</span>
                        </label>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        @if(request()->hasAny(['search', 'show_resolved']))
                            <a href="{{ route('admin.trucks.service-due') }}" class="text-sm text-gray-600 hover:text-gray-900 pb-2">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Truck NR') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current KM') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Last Service KM') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('KM Since') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Source') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Flagged') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($alerts as $alert)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $alert->truck_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {{ number_format($alert->current_km) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {{ number_format($alert->last_service_km) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-semibold text-red-600">
                                                {{ number_format($alert->km_since_service) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                                            {{ $alert->km_source ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $alert->flagged_at?->format('d.m.Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($alert->resolved_at)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('Serviced') }} {{ $alert->resolved_at->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('Needs service') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            @if(request()->boolean('show_resolved'))
                                                {{ __('No resolved alerts.') }}
                                            @else
                                                {{ __('No trucks currently need an oil service.') }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $alerts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
