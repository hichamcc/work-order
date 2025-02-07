{{-- resources/views/admin/service-templates/versions.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Template Versions') }}: {{ $template->name }}
            </h2>
            <a href="{{ route('admin.service-templates.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Templates') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <!-- Template Info -->
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Current Version Information') }}</h3>
                            <div class="mt-4 space-y-2">
                                <p><span class="font-medium">Name:</span> {{ $template->name }}</p>
                                <p><span class="font-medium">Description:</span> {{ $template->description }}</p>
                                <p><span class="font-medium">Category:</span> {{ $template->category?->name ?? 'Uncategorized' }}</p>
                                <p><span class="font-medium">Status:</span> 
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Current Checklist Items') }}</h3>
                            <div class="mt-4 space-y-2">
                                @forelse($template->checklistItems as $item)
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <span>{{ $item->description }}</span>
                                    </div>
                                @empty
                                    <p class="text-gray-500">No checklist items</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Version History -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Version History') }}</h3>
                    <div class="space-y-6">
                        @forelse($versions as $version)
                            <div class="border-l-4 border-indigo-500 pl-4 py-2 {{ !$loop->last ? 'border-b pb-6' : '' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-lg font-medium">Version {{ $version->version }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ __('Created by') }}: {{ $version->creator->name }} 
                                            | {{ $version->created_at->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                    <button type="button" 
                                            @click="$refs.items{{ $version->id }}.classList.toggle('hidden')"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('View Details') }}
                                    </button>
                                </div>

                                @if($version->change_notes)
                                    <div class="mt-2">
                                        <span class="text-sm font-medium">{{ __('Change Notes') }}:</span>
                                        <p class="text-sm text-gray-600">{{ $version->change_notes }}</p>
                                    </div>
                                @endif

                                <div x-ref="items{{ $version->id }}" class="hidden mt-4">
                                    <h5 class="text-sm font-medium mb-2">{{ __('Checklist Items in this Version') }}:</h5>
                                    <div class="space-y-2 pl-4">
                                        @foreach(json_decode($version->checklist_items) as $item)
                                            <div class="flex items-start">
                                                <svg class="h-4 w-4 text-gray-400 mt-1 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                <div>
                                                    <p class="text-sm">{{ $item->description }}</p>
                                                    @if($item->instructions)
                                                        <p class="text-xs text-gray-500">{{ $item->instructions }}</p>
                                                    @endif
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        @if($item->requires_photo)
                                                            <span class="mr-2">📷 Photo Required</span>
                                                        @endif
                                                        @if($item->is_required)
                                                            <span>⚠️ Required</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">
                                {{ __('No version history available.') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>