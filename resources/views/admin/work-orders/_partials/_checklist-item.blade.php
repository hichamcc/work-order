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
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($item->photos as $photo)
                    <a href="{{ Storage::url($photo->file_path) }}" 
                       target="_blank" 
                       class="block aspect-w-3 aspect-h-2">
                        <img src="{{ Storage::url($photo->file_path) }}" 
                             alt="Photo" 
                             class="object-cover rounded-lg">
                    </a>
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