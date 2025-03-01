{{-- resources/views/admin/parts/create-serials.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add Serial Numbers for') }}: {{ $part->name }}
            </h2>
            <a href="{{ route('admin.parts.show', $part) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Part') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-center">
                            <div class="w-1/4 font-medium">Part Number:</div>
                            <div>{{ $part->part_number }}</div>
                        </div>
                        <div class="flex items-center mt-2">
                            <div class="w-1/4 font-medium">Initial Stock:</div>
                            <div>{{ $part->stock }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.parts.serials.store', $part) }}" class="space-y-6">
                        @csrf

                        <div class="border-t border-gray-200 pt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Enter Serial Numbers</h3>
                            
                            <div id="serial_inputs" class="space-y-3">
                                @for ($i = 0; $i < $part->stock; $i++)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-10 text-center">{{ $i + 1 }}.</div>
                                        <x-text-input 
                                            name="serial_numbers[]" 
                                            type="text" 
                                            class="block w-full" 
                                            placeholder="Enter serial number"
                                            required />
                                    </div>
                                @endfor
                            </div>
                            
                            <div class="mt-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                        name="generate_barcodes" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                        value="1"
                                        checked>
                                    <span class="ml-2 text-sm text-gray-700">{{ __('Generate Printable Barcodes') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button type="button" 
                                    onclick="bulkFill()"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Generate Sequential Numbers') }}
                            </button>
                            
                            <x-primary-button>
                                {{ __('Save Serial Numbers') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function bulkFill() {
            const prefix = '{{ substr($part->part_number, 0, 3) }}';
            const inputs = document.querySelectorAll('input[name="serial_numbers[]"]');
            
            inputs.forEach((input, index) => {
                const paddedNumber = String(index + 1).padStart(8, '0');
                input.value = prefix.toUpperCase() + paddedNumber;
            });
        }
    </script>
</x-app-layout>