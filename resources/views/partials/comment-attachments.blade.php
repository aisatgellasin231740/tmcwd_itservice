{{-- Inline attachments for a comment (if any) --}}
@if($comment->attachments && $comment->attachments->isNotEmpty())
<div class="mt-2 space-y-1">
    @foreach($comment->attachments as $att)
    <a href="{{ route('attachments.download', $att) }}"
       class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/60 border border-gray-200
              rounded-lg text-xs text-blue-700 hover:bg-white hover:text-blue-900 transition group">
        <svg class="w-3.5 h-3.5 text-blue-400 shrink-0 group-hover:text-blue-600"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        <span class="truncate max-w-[160px]">{{ $att->original_name }}</span>
        <span class="text-gray-400 shrink-0">{{ $att->human_size }}</span>
    </a>
    @endforeach
</div>
@endif
