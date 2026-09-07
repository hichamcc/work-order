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

    <div class="py-12" x-data="truckServiceList()">
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
                        {{ __('Oil service status per truck, from recorded services and imported Mapon reminders. Updated automatically every day at 04:00.') }}
                    </div>

                    @unless(request()->boolean('show_resolved'))
                        <div class="mb-6 border-b border-gray-200">
                            <nav class="-mb-px flex space-x-6">
                                @foreach([
                                    'overdue' => __('Overdue'),
                                    'attention' => __('Needs attention'),
                                    'all' => __('All tracked'),
                                ] as $key => $label)
                                    <a href="{{ route('admin.trucks.service-due', array_merge(request()->except(['filter', 'page']), ['filter' => $key])) }}"
                                       class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium {{ $filter === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                        {{ $label }}
                                        <span class="ml-1 text-xs {{ $key === 'overdue' && $counts[$key] > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                            {{ $counts[$key] }}
                                        </span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endunless

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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Truck NR') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current KM') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Due at KM') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('KM Remaining') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Last Service KM') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Source') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Flagged') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Note') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($alerts as $alert)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $alert->truck_number }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-700">
                                            {{ number_format($alert->current_km) }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-700">
                                            {{ $alert->due_at_km !== null ? number_format($alert->due_at_km) : '—' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($alert->km_remaining === null)
                                                <span class="text-gray-400">—</span>
                                            @elseif($alert->km_remaining <= 0)
                                                <span class="font-semibold text-red-600">
                                                    {{ number_format(abs($alert->km_remaining)) }} {{ __('over') }}
                                                </span>
                                            @else
                                                <span class="font-semibold text-gray-700">
                                                    {{ number_format($alert->km_remaining) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($alert->last_service_km !== null)
                                                {{ number_format($alert->last_service_km) }}
                                                <span class="block text-xs text-gray-400">
                                                    {{ number_format($alert->km_since_service) }} {{ __('km since') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400" title="{{ __('Imported from a Mapon reminder; no service recorded in the system yet.') }}">
                                                    {{ __('Not recorded') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                                            {{ $alert->km_source ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $alert->flagged_at?->format('d.m.Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($alert->resolved_at)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('Serviced') }} {{ $alert->resolved_at->format('d.m.Y') }}
                                                </span>
                                            @elseif($alert->service_state === 'overdue')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('Overdue') }}
                                                </span>
                                            @elseif($alert->service_state === 'due_soon')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ __('Due soon') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                                    {{ __('Upcoming') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 max-w-xs">
                                            @if($alert->note)
                                                {{-- The workshop's own note leads; the derived due line follows it. --}}
                                                @foreach(array_filter(explode("\n", $alert->note)) as $i => $line)
                                                    <span class="block {{ $i === 0 ? 'text-gray-800' : 'text-xs text-gray-400' }}"
                                                          title="{{ trim($line) }}">
                                                        {{ trim($line) }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                            @unless($alert->resolved_at)
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button"
                                                            @click="edit({{ $alert->id }}, '{{ $alert->truck_number }}', {{ (int) $alert->due_at_km }}, @js($alert->note))"
                                                            class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                                        {{ __('Edit') }}
                                                    </button>

                                                    @if($alert->workOrder)
                                                        <a href="{{ route('admin.work-orders.show', $alert->workOrder) }}"
                                                           class="text-indigo-600 hover:text-indigo-900">
                                                            #{{ $alert->workOrder->id }}
                                                            <span class="block text-xs text-gray-400">
                                                                {{ $alert->workOrder->assignedTo->name ?? '—' }}
                                                            </span>
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                                @click="assign({{ $alert->id }}, '{{ $alert->truck_number }}')"
                                                                class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                                            {{ __('Assign') }}
                                                        </button>
                                                    @endif

                                                    <button type="button"
                                                            @click="markServiced({{ $alert->id }}, '{{ $alert->truck_number }}')"
                                                            class="px-3 py-1 rounded-md bg-green-600 text-white hover:bg-green-700">
                                                        {{ __('Serviced') }}
                                                    </button>
                                                </div>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
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

        <!-- Assign to a worker -->
        <div x-show="showAssign" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
             @click.self="showAssign = false" @keydown.escape.window="showAssign = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <form :action="assignUrl" method="POST" class="p-6">
                    @csrf
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('Assign oil service') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="truckNumber"></p>

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

        <!-- Correct the service point or note -->
        <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
             @click.self="showEdit = false" @keydown.escape.window="showEdit = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <form :action="editUrl" method="POST" class="p-6">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('Edit service details') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="truckNumber"></p>

                    <div class="mt-4">
                        <x-input-label for="edit_due" :value="__('Due at KM')" />
                        <x-text-input type="number" id="edit_due" name="due_at_km" min="0" step="1" required
                                      class="mt-1 block w-full" x-model="editDueAt" />
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('The odometer this truck is next due for an oil service at. KM remaining is worked out from the live Mapon reading.') }}
                        </p>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="edit_note" :value="__('Note')" />
                        <textarea id="edit_note" name="note" rows="3" x-model="editNote"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showEdit = false"
                                class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </button>
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
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
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('Record oil service') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="truckNumber"></p>

                    <div class="mt-4">
                        <x-input-label for="complete_km" :value="__('KM at service')" />
                        <x-text-input type="number" id="complete_km" name="km" min="0" step="1"
                                      class="mt-1 block w-full" x-model="completeKm" />
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Leave blank to use the current reading from Mapon.') }}
                        </p>
                    </div>

                    <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded p-3">
                        {{ __('This records the service and sets the next one due') }}
                        <strong>{{ number_format($intervalKm) }} km</strong>
                        {{ __('later.') }}
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
        function truckServiceList() {
            return {
                showAssign: false,
                showComplete: false,
                showEdit: false,
                truckNumber: '',
                completeKm: '',
                editDueAt: '',
                editNote: '',
                assignUrl: '',
                completeUrl: '',
                editUrl: '',

                edit(id, truck, dueAt, note) {
                    this.truckNumber = truck;
                    this.editDueAt = dueAt;
                    this.editNote = note || '';
                    this.editUrl = '{{ url('admin/trucks/alerts') }}/' + id;
                    this.showEdit = true;
                },

                assign(id, truck) {
                    this.truckNumber = truck;
                    this.assignUrl = '{{ url('admin/trucks/alerts') }}/' + id + '/assign';
                    this.showAssign = true;
                },

                markServiced(id, truck) {
                    this.truckNumber = truck;
                    this.completeKm = '';
                    this.completeUrl = '{{ url('trucks/alerts') }}/' + id + '/complete';
                    this.showComplete = true;
                },
            }
        }
    </script>
    @endpush
</x-app-layout>
