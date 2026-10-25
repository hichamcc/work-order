{{-- resources/views/admin/trailers/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trailers') }}
            </h2>
            <a href="{{ route('admin.trailers.history') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Service History') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="trailerList()">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="mb-4 text-sm text-gray-600">
                        {{ __('Trailers are not tracked in Mapon, so there is no mileage. Mark a trailer when it needs service and it stays on this list until the work is recorded.') }}
                    </div>

                    <div class="mb-6 border-b border-gray-200">
                        <nav class="-mb-px flex space-x-6">
                            @foreach(['needs_service' => __('Needs service'), 'all' => __('All trailers')] as $key => $label)
                                <a href="{{ route('admin.trailers.index', array_merge(request()->except(['filter', 'page']), ['filter' => $key])) }}"
                                   class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium {{ $filter === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    {{ $label }}
                                    <span class="ml-1 text-xs {{ $key === 'needs_service' && $counts[$key] > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                        {{ $counts[$key] }}
                                    </span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <div>
                            <x-input-label for="search" :value="__('Trailer plate')" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block"
                                :value="request('search')" placeholder="{{ __('Search trailer') }}" />
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.trailers.index', ['filter' => $filter]) }}" class="text-sm text-gray-600 hover:text-gray-900 pb-2">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Trailer') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Note') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Flagged') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Last serviced') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($trailers as $trailer)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $trailer->display_name }}
                                            @if($trailer->label && $trailer->label !== $trailer->plate_number)
                                                <span class="block text-xs font-normal text-gray-400">{{ $trailer->plate_number }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($trailer->needs_service)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('Needs service') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                                    {{ __('OK') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 max-w-xs">
                                            @if($trailer->service_note)
                                                <span class="block truncate" title="{{ $trailer->service_note }}">{{ $trailer->service_note }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($trailer->flagged_at)
                                                {{ $trailer->flagged_at->format('d.m.Y') }}
                                                <span class="block text-xs text-gray-400">{{ $trailer->flaggedBy->name ?? '—' }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $trailer->last_serviced_at?->format('d.m.Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($trailer->needs_service)
                                                    @if($trailer->workOrder)
                                                        <a href="{{ route('admin.work-orders.show', $trailer->workOrder) }}"
                                                           class="text-indigo-600 hover:text-indigo-900">
                                                            #{{ $trailer->workOrder->id }}
                                                            <span class="block text-xs text-gray-400">
                                                                {{ $trailer->workOrder->assignedTo->name ?? '—' }}
                                                            </span>
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                                @click="assign({{ $trailer->id }}, @js($trailer->display_name))"
                                                                class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                                            {{ __('Assign') }}
                                                        </button>
                                                    @endif

                                                    <button type="button"
                                                            @click="markServiced({{ $trailer->id }}, @js($trailer->display_name))"
                                                            class="px-3 py-1 rounded-md bg-green-600 text-white hover:bg-green-700">
                                                        {{ __('Serviced') }}
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            @click="flag({{ $trailer->id }}, @js($trailer->display_name))"
                                                            class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                                        {{ __('Mark needs service') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            @if($filter === 'needs_service')
                                                {{ __('No trailers currently need service.') }}
                                            @else
                                                {{ __('No trailers yet. Import the list exported from WTTsystem.') }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $trailers->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Mark needs service -->
        <div x-show="showFlag" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
             @click.self="showFlag = false" @keydown.escape.window="showFlag = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <form :action="flagUrl" method="POST" class="p-6">
                    @csrf
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Mark as needing service') }}</h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="plateNumber"></p>

                    <div class="mt-4">
                        <x-input-label for="flag_note" :value="__('What needs doing? (optional)')" />
                        <textarea id="flag_note" name="service_note" rows="3"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showFlag = false"
                                class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </button>
                        <x-primary-button>{{ __('Mark needs service') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assign to a worker -->
        <div x-show="showAssign" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
             @click.self="showAssign = false" @keydown.escape.window="showAssign = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <form :action="assignUrl" method="POST" class="p-6">
                    @csrf
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Assign trailer service') }}</h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="plateNumber"></p>

                    <div class="mt-4">
                        <x-input-label for="assign_worker" :value="__('Assign to')" />
                        <select id="assign_worker" name="assigned_to" required
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('Select worker') }}</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="assign_due" :value="__('Due date (optional)')" />
                        <x-text-input type="date" id="assign_due" name="due_date" class="mt-1 block w-full" />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showAssign = false"
                                class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </button>
                        <x-primary-button>{{ __('Create work order') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mark serviced -->
        <div x-show="showComplete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
             @click.self="showComplete = false" @keydown.escape.window="showComplete = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <form :action="completeUrl" method="POST" class="p-6">
                    @csrf
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Record trailer service') }}</h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="plateNumber"></p>

                    <div class="mt-4">
                        <x-input-label for="complete_note" :value="__('What was done? (optional)')" />
                        <textarea id="complete_note" name="note" rows="3"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded p-3">
                        {{ __('This records the service and takes the trailer off the list.') }}
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showComplete = false"
                                class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </button>
                        <button class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">
                            {{ __('Record service') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function trailerList() {
            return {
                showFlag: false,
                showAssign: false,
                showComplete: false,
                plateNumber: '',
                flagUrl: '',
                assignUrl: '',
                completeUrl: '',

                flag(id, plate) {
                    this.plateNumber = plate;
                    this.flagUrl = '{{ url('admin/trailers') }}/' + id + '/flag';
                    this.showFlag = true;
                },

                assign(id, plate) {
                    this.plateNumber = plate;
                    this.assignUrl = '{{ url('admin/trailers') }}/' + id + '/assign';
                    this.showAssign = true;
                },

                markServiced(id, plate) {
                    this.plateNumber = plate;
                    this.completeUrl = '{{ url('trailers') }}/' + id + '/complete';
                    this.showComplete = true;
                },
            }
        }
    </script>
    @endpush
</x-app-layout>
