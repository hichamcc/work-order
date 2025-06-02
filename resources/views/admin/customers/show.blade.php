<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer Details') }} - {{ $customer->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.customers.edit', $customer) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Customer
                </a>
                <a href="{{ route('admin.customers.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customers
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Customer Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $customer->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->email }}
                                </a>
                            </p>
                        </div>

                        @if($customer->phone)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a href="tel:{{ $customer->phone }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $customer->phone }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                @if($customer->is_default)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Default Customer
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Regular Customer
                                    </span>
                                @endif
                            </p>
                        </div>

                        @if($customer->address)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->address }}</p>
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Member Since</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('F j, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Work Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Work Orders</h3>
                        <span class="text-sm text-gray-500">
                            Total: {{ $customer->workOrders()->count() }} work orders
                        </span>
                    </div>

                    @if($customer->workOrders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Priority
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($customer->workOrders as $workOrder)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $workOrder->title }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ Str::limit($workOrder->description, 50) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $workOrder->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($workOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                                       ($workOrder->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 
                                                       'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $workOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : 
                                                       ($workOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                                       ($workOrder->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                                                       'bg-green-100 text-green-800')) }}">
                                                    {{ ucfirst($workOrder->priority) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $workOrder->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.work-orders.show', $workOrder) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($customer->workOrders()->count() > 10)
                            <div class="mt-4 text-center">
                                <a href="{{ route('admin.work-orders.index', ['customer' => $customer->id]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    View all work orders for this customer
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No work orders found for this customer.</p>
                            <a href="{{ route('admin.work-orders.create', ['customer' => $customer->id]) }}" 
                               class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                Create the first work order
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>