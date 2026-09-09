@extends('layouts.app')
@section('title', $ticket->ticket_number)

@section('content')
{{-- Header --}}
<div class="mb-5">
    <a href="{{ route('requester.tickets.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to My Tickets
    </a>
    <div class="flex flex-wrap items-start gap-3">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-mono text-sm font-semibold text-gray-500">{{ $ticket->ticket_number }}</span>
                <x-ticket-badge :status="$ticket->status"/>
                <x-ticket-badge :priority="$ticket->priority->name"/>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->title }}</h1>
        </div>
        {{-- Requester actions — only while Open --}}
        @can('editOwn', $ticket)
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('requester.tickets.edit', $ticket) }}"
               class="btn-secondary btn-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('requester.tickets.cancel', $ticket) }}"
                  x-data
                  @submit.prevent="
                    if (confirm('Withdraw this ticket? It will be marked closed and cannot be reopened from this action.'))
                        $el.submit()
                  ">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg
                               border border-red-200 text-red-600 bg-white hover:bg-red-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Withdraw
                </button>
            </form>
        </div>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Main --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Description --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Description</h2></div>
            <div class="card-body">
                <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</p>
            </div>
        </div>

        {{-- Attachments --}}
        @if($ticket->attachments->isNotEmpty())
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Attachments</h2></div>
            <div class="card-body space-y-2">
                @foreach($ticket->attachments as $att)
                <a href="{{ route('attachments.download', $att) }}"
                   class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 border border-gray-100 text-sm group">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <span class="flex-1 truncate text-blue-700 group-hover:underline">{{ $att->original_name }}</span>
                    <span class="text-gray-400 text-xs shrink-0">{{ $att->human_size }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Conversation --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Conversation</h2></div>
            <div class="card-body space-y-4">
                @forelse($comments as $comment)
                @php $isOwn = $comment->user_id === auth()->id(); @endphp
                <div class="flex gap-3 {{ $isOwn ? 'flex-row-reverse' : '' }}">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-semibold text-blue-700 shrink-0">
                        {{ $comment->user->initials }}
                    </div>
                    <div class="max-w-[80%]">
                        <div class="flex items-baseline gap-2 {{ $isOwn ? 'justify-end' : '' }} mb-1">
                            <span class="text-xs font-semibold text-gray-700">{{ $comment->user->name }}</span>
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="rounded-xl px-4 py-3 text-sm leading-relaxed
                            {{ $isOwn ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                            {{ $comment->body }}
                        </div>
                        @include('partials.comment-attachments', ['comment' => $comment])
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-400 text-sm py-4">No replies yet. The IT team will respond here.</p>
                @endforelse
            </div>

            {{-- Reply form --}}
            @if(!$ticket->isClosed())
            <div class="px-6 pb-6 pt-2 border-t border-gray-100">
                {{-- ── Reopen banner (resolved tickets only) ───────────────── --}}
                @can('reopen', $ticket)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
                     x-data="{ showForm: false }">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-amber-800">This ticket has been marked as resolved.</p>
                            <p class="text-xs text-amber-700 mt-0.5">If your issue is not fixed, you can request it to be reopened.</p>
                        </div>
                        <button type="button" @click="showForm = !showForm"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg
                                       border border-amber-400 text-amber-800 bg-white hover:bg-amber-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Not Resolved? Reopen
                        </button>
                    </div>

                    <form method="POST" action="{{ route('requester.tickets.reopen', $ticket) }}"
                          x-show="showForm" x-cloak class="mt-3 space-y-2">
                        @csrf
                        <label for="reopen_reason" class="block text-xs font-medium text-amber-900">
                            Please explain why this issue is not resolved <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reopen_reason" name="reopen_reason" rows="3"
                                  placeholder="e.g. The printer still shows the same error after the fix applied..."
                                  class="form-textarea text-sm @error('reopen_reason') border-red-400 @enderror">{{ old('reopen_reason') }}</textarea>
                        @error('reopen_reason')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg
                                           bg-amber-600 hover:bg-amber-700 text-white transition-colors">
                                Submit Reopen Request
                            </button>
                            <button type="button" @click="showForm = false"
                                    class="btn-secondary btn-sm">Cancel</button>
                        </div>
                    </form>
                </div>
                @endcan

                <form method="POST" action="{{ route('requester.tickets.comments.store', $ticket) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <label class="form-label">Add a Reply</label>
                    <textarea name="body" rows="3"
                              placeholder="Add a comment or update..."
                              class="form-textarea @error('body') border-red-400 @enderror mb-2">{{ old('body') }}</textarea>
                    @error('body')<p class="form-error">{{ $message }}</p>@enderror

                    {{-- File attachment --}}
                    <div class="mb-3" x-data="{ files: [] }">
                        <label class="block text-xs text-gray-500 mb-1">
                            Attach files (optional — max 10 MB each)
                        </label>
                        <input type="file" name="attachments[]" multiple
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                               @change="files = Array.from($event.target.files)"
                               class="text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg
                                      file:border file:border-gray-300 file:text-xs file:bg-white
                                      file:text-gray-700 hover:file:bg-gray-50">
                        <ul x-show="files.length" class="mt-1 space-y-0.5">
                            <template x-for="f in files" :key="f.name">
                                <li class="text-[11px] text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    <span x-text="f.name"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <button type="submit" class="btn-primary btn-sm">Send Reply</button>
                </form>
            </div>
            @else
            <div class="px-6 pb-4 pt-2 border-t border-gray-100">
                <p class="text-xs text-gray-400 text-center">This ticket is closed. Please submit a new request if needed.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Details</h2></div>
            <div class="card-body text-sm divide-y divide-gray-50 space-y-0 -my-1">
                @php $rows = [
                    ['Department',  $ticket->department->name],
                    ['Category',    $ticket->category->name],
                    ['Submitted',   $ticket->created_at->format('M d, Y g:i A')],
                    ['Last Updated',$ticket->updated_at->diffForHumans()],
                    ['Assigned To', $ticket->assignee?->name ?? 'Pending assignment'],
                ]; @endphp
                @foreach($rows as [$key,$val])
                <div class="flex justify-between py-2.5 gap-2">
                    <span class="text-gray-500 shrink-0">{{ $key }}</span>
                    <span class="font-medium text-right text-gray-800">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Activity</h2></div>
            <div class="card-body">
                <ol class="space-y-3">
                    @foreach($ticket->activities as $activity)
                    <li class="flex gap-3 text-xs">
                        <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                        </div>
                        <div>
                            <p class="text-gray-700">{{ $activity->description_label }}</p>
                            <p class="text-gray-400">{{ $activity->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
