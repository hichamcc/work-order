{{-- resources/views/admin/parts/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Parts Management') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.parts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ __('Add New Part') }}
                </a>
                <a href="{{ route('admin.parts.export') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ __('Export') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Parts</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_parts'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Active Parts</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['active_parts'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Out of Stock</div>
                        <div class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['out_of_stock'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Inventory Value</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format($stats['total_value'], 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                <form action="{{ route('admin.parts.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" 
                            :value="request('search')" placeholder="Name or part number..." />
                    </div>
                    
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="stock_status" :value="__('Stock Status')" />
                        <select name="stock_status" id="stock_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Stock Status</option>
                            <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <x-primary-button type="submit">
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('admin.parts.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($parts as $part)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $part->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $part->part_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $part->stock }}</div>
                                        @if($part->stock <= 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Out of Stock
                                            </span>
                                        @elseif($part->stock <= 10)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Low Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${{ number_format($part->cost, 2) }}</div>
                                        <div class="text-sm text-gray-500">Value: ${{ number_format($part->stock * $part->cost, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $part->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $part->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('admin.parts.edit', $part) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 px-3 py-1 rounded-md">
                                                Edit
                                            </a>

                                            <button type="button" 
                                                    onclick="Livewire.dispatch('openModal', { component: 'adjust-stock-modal', arguments: { partId: {{ $part->id }} }})"
                                                    class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded-md">
                                                Adjust Stock
                                            </button>

                                            <a href="{{ route('admin.parts.stock-history', $part) }}" 
                                                class="text-purple-600 hover:text-purple-900 bg-purple-100 hover:bg-purple-200 px-3 py-1 rounded-md">
                                                    History
                                            </a>

                                            <form action="{{ route('admin.parts.toggle-status', $part) }}" 
                                                  method="POST" 
                                                  class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="{{ $part->is_active ? 'text-yellow-600 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200' : 'text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200' }} px-3 py-1 rounded-md">
                                                    {{ $part->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.parts.destroy', $part) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this part?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No parts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($parts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $parts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    @push('scripts')
    <script>
    document.addEventListener('stock-adjusted', function() {
        location.reload();  // Reload the page when the event is triggered
    });
</script>
    @endpush

</x-app-layout>