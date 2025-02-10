{{-- resources/views/admin/work-orders/_partials/_status-badge.blade.php --}}
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
    {{ $status === 'completed' ? 'bg-green-100 text-green-800' : 
       ($status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
       ($status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 
       'bg-gray-100 text-gray-800')) }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>