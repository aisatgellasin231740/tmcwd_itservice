@extends('layouts.app')
@section('title', $ticket->ticket_number . ' — Admin')

@section('content')
<div class="mb-5">
    <a href="{{ route('admin.tickets.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to All Tickets
    </a>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-mono text-sm font-semibold text-gray-500">{{ $ticket->ticket_number }}</span>
                <x-ticket-badge :status="$ticket->status"/>
                <x-ticket-badge :priority="$ticket->priority->name"/>
                <x-sla-indicator :ticket="$ticket"/>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->title }}</h1>
        </div>
        <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}"
              onsubmit="return confirm('Permanently delete this ticket?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger btn-sm">Delete Ticket</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        {{-- Description --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Description</h2>
                <span class="text-xs text-gray-400">{{ $ticket->requester->name }} · {{ $ticket->created_at->format('M d, Y g:i A') }}</span>
            </div>
            <div class="card-body">
                <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</p>
            </div>
        </div>

        @if($ticket->attachments->isNotEmpty())
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Attachments</h2></div>
            <div class="card-body space-y-2">
                @foreach($ticket->attachments as $att)
                <a href="{{ route('attachments.download', $att) }}"
                   class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 border border-gray-100 text-sm group">
                    <svg class="w-5 h-5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <span class="flex-1 truncate text-blue-700 group-hover:underline">{{ $att->original_name }}</span>
                    <span class="text-gray-400 text-xs">{{ $att->human_size }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- All Comments (admin sees everything) --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Conversation</h2></div>
            <div class="card-body space-y-4">
                @forelse($ticket->comments as $comment)
                @php $isOwn = $comment->user_id === auth()->id(); @endphp
                <div class="flex gap-3 {{ $isOwn ? 'flex-row-reverse' : '' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold shrink-0
                        {{ $comment->is_internal ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $comment->user->initials }}
                    </div>
                    <div class="max-w-[80%]">
                        <div class="flex items-baseline gap-2 {{ $isOwn?'justify-end':'' }} mb-1">
                            <span class="text-xs font-semibold text-gray-700">{{ $comment->user->name }}</span>
                            @if($comment->is_internal)
                                <span class="badge bg-orange-100 text-orange-700 text-[10px]">Internal</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="rounded-xl px-4 py-3 text-sm
                            {{ $comment->is_internal
                                ? 'bg-orange-50 border border-orange-200 text-orange-900'
                                : ($isOwn ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                            {{ $comment->body }}
                        </div>
                        @include('partials.comment-attachments', ['comment' => $comment])
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-400 text-sm py-4">No comments yet.</p>
                @endforelse
            </div>
            <div class="px-6 pb-6 pt-3 border-t border-gray-100" x-data="{ type: 'public' }">
                <form method="POST" action="{{ route('admin.tickets.comments.store', $ticket) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-2 mb-3">
                        <button type="button" @click="type='public'"
                                :class="type==='public' ? 'btn-primary btn-sm' : 'btn-secondary btn-sm'">Public Reply</button>
                        <button type="button" @click="type='internal'"
                                :class="type==='internal' ? 'bg-orange-500 text-white px-3 py-1.5 text-xs font-medium rounded-lg' : 'btn-secondary btn-sm'">Internal Note</button>
                    </div>
                    <input type="hidden" name="is_internal" :value="type==='internal'?'1':'0'">
                    <textarea name="body" rows="3"
                              :placeholder="type==='internal' ? 'Add an internal note...' : 'Write a reply...'"
                              class="form-textarea mb-2"></textarea>
                    <div class="mb-2" x-data="{ files: [] }">
                        <label class="block text-xs text-gray-500 mb-1">Attach files (optional)</label>
                        <input type="file" name="attachments[]" multiple
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                               @change="files = Array.from($event.target.files)"
                               class="text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg
                                      file:border file:border-gray-300 file:text-xs file:bg-white
                                      file:text-gray-700 hover:file:bg-gray-50">
                        <ul x-show="files.length" class="mt-1 space-y-0.5">
                            <template x-for="f in files" :key="f.name">
                                <li class="text-[11px] text-gray-500" x-text="f.name"></li>
                            </template>
                        </ul>
                    </div>
                    <button type="submit"
                            :class="type==='internal' ? 'bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 text-sm font-medium rounded-lg' : 'btn-primary'"
                            x-text="type==='internal' ? 'Save Note' : 'Send Reply'"></button>
                </form>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        {{-- Admin Actions --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Actions</h2></div>
            <div class="card-body space-y-3">
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
                    @csrf @method('PATCH')
                    <label class="form-label">Status</label>
                    <div class="flex gap-2">
                        <select name="status" class="form-select flex-1">
                            @foreach(['open'=>'Open','in_progress'=>'In Progress','on_hold'=>'On Hold','resolved'=>'Resolved','closed'=>'Closed'] as $v=>$l)
                                <option value="{{ $v }}" {{ $ticket->status===$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary btn-sm shrink-0">Save</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
                    @csrf @method('PATCH')
                    <label class="form-label">Assign To</label>
                    <div class="flex gap-2">
                        <select name="assigned_to" class="form-select flex-1">
                            <option value="">Unassigned</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ $ticket->assigned_to===$ag->id?'selected':'' }}>
                                    {{ $ag->name }}
                                    @if($ag->hasRole('it_head')) (IT Head) @else (IT Staff) @endif
                                    {{ $ag->id === auth()->id() ? '— Me' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary btn-sm shrink-0">Save</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
                    @csrf @method('PATCH')
                    <label class="form-label">Priority</label>
                    <div class="flex gap-2">
                        <select name="priority_id" class="form-select flex-1">
                            @foreach($priorities as $p)
                                <option value="{{ $p->id }}" {{ $ticket->priority_id===$p->id?'selected':'' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary btn-sm shrink-0">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Details --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Details</h2></div>
            <div class="card-body text-sm divide-y divide-gray-50">
                @php $rows=[
                    ['Requester', $ticket->requester->name . ' (' . $ticket->requester->department?->name . ')'],
                    ['Department', $ticket->department->name],
                    ['Category', $ticket->category->name],
                    ['SLA Due', $ticket->sla_due_at?->format('M d, Y H:i') ?? '—'],
                    ['Submitted', $ticket->created_at->format('M d, Y g:i A')],
                    ['Resolved', $ticket->resolved_at?->format('M d, Y g:i A') ?? '—'],
                    ['Closed', $ticket->closed_at?->format('M d, Y g:i A') ?? '—'],
                ]; @endphp
                @foreach($rows as [$k,$v])
                <div class="flex justify-between py-2 gap-2">
                    <span class="text-gray-500 text-xs shrink-0">{{ $k }}</span>
                    <span class="text-right text-xs font-medium text-gray-800">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Activity --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-700">Activity Log</h2></div>
            <div class="card-body max-h-60 overflow-y-auto">
                <ol class="space-y-3">
                    @foreach($ticket->activities as $act)
                    <li class="flex gap-2 text-xs">
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-gray-700">{{ $act->description_label }}</p>
                            <p class="text-gray-400">{{ $act->created_at->format('M d, Y g:i A') }}
                                @if($act->user) · {{ $act->user->name }} @endif
                            </p>
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
