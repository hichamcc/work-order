{{-- resources/views/admin/work-orders/_partials/_checklist-item.blade.php --}}
<div class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg">
    <div class="flex-shrink-0">
        @if($item->is_completed)
            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        @else
            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
        @endif
    </div>
    <div class="flex-1">
        <div class="text-sm font-medium text-gray-900">{{ $item->checklistItem->description }}</div>
        @if($item->checklistItem->instructions)
            <div class="mt-1 text-sm text-gray-500">{{ $item->checklistItem->instructions }}</div>
        @endif
        @if($item->checklistItem->photo_instructions && $item->checklistItem->requires_photo)
            <div class="mt-1 text-sm text-gray-500">📷  {{ $item->checklistItem->photo_instructions }}</div>
        @endif
        
        @if($item->is_completed)
            <div class="mt-2 text-xs text-gray-500">
                Completed by {{ $item->completedByUser->name }} 
                on {{ $item->completed_at->format('M d, Y H:i') }}
            </div>
            @if($item->notes)
                <div class="mt-1 text-sm text-gray-600">{{ $item->notes }}</div>
            @endif
        @endif

        @if($item->photos->count() > 0)
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach($item->photos as $photo)
                    <div class="relative group">
                        <div class="aspect-w-4 aspect-h-3 w-full overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ Storage::url($photo->file_path) }}" 
                                 alt="Checklist item photo"
                                 class="h-full w-full object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all">
                                <a href="{{ Storage::url($photo->file_path) }}" 
                                   target="_blank"
                                   class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="bg-white bg-opacity-75 rounded-full p-2">
                                        <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </span>
                                </a>
                            </div>
                        </div>
                        @if($photo->file_name)
                            <div class="mt-1 text-xs text-gray-500 truncate">
                                {{ $photo->file_name }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="flex-shrink-0 flex flex-col space-y-2">
        @if($item->checklistItem->requires_photo)
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                📷 Photo Required
            </span>
        @endif
        @if($item->checklistItem->is_required)
            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                Required
            </span>
        @endif
    </div>
</div>