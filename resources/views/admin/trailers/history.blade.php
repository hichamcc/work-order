{{-- resources/views/admin/trailers/history.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trailer Service History') }}
            </h2>
            <a href="{{ route('admin.trailers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Trailers') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                        <div>
                            <x-input-label for="trailer_id" :value="__('Trailer')" />
                            <select id="trailer_id" name="trailer_id"
                                class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('All trailers') }}</option>
                                @foreach($trailers as $trailer)
                                    <option value="{{ $trailer->id }}" {{ (string) request('trailer_id') === (string) $trailer->id ? 'selected' : '' }}>
                                        {{ $trailer->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        @if(request()->filled('trailer_id'))
                            <a href="{{ route('admin.trailers.history') }}" class="text-sm text-gray-600 hover:text-gray-900 pb-2">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Trailer') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Note') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Work Order') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Recorded By') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($services as $service)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $service->trailer->display_name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $service->serviced_at?->format('d.m.Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 max-w-md">
                                            {{ $service->note ?: '—' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            @if($service->workOrder)
                                                <a href="{{ route('admin.work-orders.show', $service->workOrder) }}"
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    #{{ $service->workOrder->id }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $service->recordedBy->name ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            {{ __('No trailer services recorded yet.') }}
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
