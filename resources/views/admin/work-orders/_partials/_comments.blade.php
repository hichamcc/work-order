{{-- resources/views/admin/work-orders/_partials/_comments.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Comments</h3>
        
        @if($comments->count() > 0)
            <div class="space-y-6">
                @foreach($comments as $comment)
                    <div class="flex space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-lg font-medium text-gray-600">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                <span class="text-gray-500 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-700">
                                {{ $comment->comment }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">No comments yet.</p>
        @endif
    </div>
</div>