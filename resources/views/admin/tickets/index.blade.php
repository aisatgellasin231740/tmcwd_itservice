@extends('layouts.app')
@section('title', 'All Tickets')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">All Tickets</h1>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-36">
                    <option value="">All</option>
                    @foreach(['open'=>'Open','in_progress'=>'In Progress','on_hold'=>'On Hold','resolved'=>'Resolved','closed'=>'Closed'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Priority</label>
                <select name="priority_id" class="form-select w-28">
                    <option value="">All</option>
                    @foreach($priorities as $p)
                        <option value="{{ $p->id }}" {{ request('priority_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select w-40">
                    <option value="">All</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select w-44">
                    <option value="">All</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">IT Staff</label>
                <select name="assigned_to" class="form-select w-36">
                    <option value="">All</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('assigned_to')==$ag->id?'selected':'' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket # or title" class="form-input w-40">
            </div>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['status','priority_id','department_id','category_id','assigned_to','search']))
                <a href="{{ route('admin.tickets.index') }}" class="btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Bulk form --}}
<form method="POST" action="{{ route('tickets.bulk') }}" id="adminBulkForm"
      x-data="bulkActions()" @submit.prevent="confirmBulk($event)">
    @csrf

    {{-- Bulk Action Bar --}}
    <div x-show="selected.length > 0" x-cloak x-transition
         class="mb-3 flex flex-wrap items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl">
        <span class="text-sm font-semibold text-blue-800">
            <span x-text="selected.length"></span> ticket(s) selected
        </span>
        <div class="flex flex-wrap gap-2 ml-2">
            <div class="flex items-center gap-1">
                <select id="adminBulkAssignTo" class="form-select text-xs py-1 h-8">
                    <option value="">Assign to…</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                    @endforeach
                </select>
                <button type="button" @click="submitBulk('assign', document.getElementById('adminBulkAssignTo').value)"
                        class="btn-primary btn-sm">Assign</button>
            </div>
            <button type="button" @click="submitBulk('resolve')"
                    class="btn-secondary btn-sm text-green-700 border-green-300 hover:bg-green-50">
                Mark Resolved
            </button>
            <button type="button" @click="submitBulk('close')"
                    class="btn-secondary btn-sm">Close</button>
            <div class="flex items-center gap-1">
                <select id="adminBulkPriority" class="form-select text-xs py-1 h-8">
                    <option value="">Set priority…</option>
                    @foreach($priorities as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <button type="button" @click="submitBulk('priority', null, document.getElementById('adminBulkPriority').value)"
                        class="btn-secondary btn-sm">Apply</button>
            </div>
        </div>
        <button type="button" @click="clearAll()" class="ml-auto text-xs text-blue-500 hover:underline">
            Clear selection
        </button>
        <input type="hidden" name="action" x-bind:value="pendingAction">
        <input type="hidden" name="assigned_to" x-bind:value="pendingAssignTo">
        <input type="hidden" name="priority_id" x-bind:value="pendingPriority">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ticket_ids[]" :value="id">
        </template>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            @php
                $sort = request('sort', 'created_at');
                $dir  = request('dir', 'desc');
                $toggleDir = $dir === 'asc' ? 'desc' : 'asc';
                $sortUrl  = fn(string $field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $sort === $field ? $toggleDir : 'desc', 'page' => 1]);
                $sortIcon = fn(string $field) => $sort === $field ? ($dir === 'asc' ? ' ↑' : ' ↓') : ' ↕';
            @endphp
            <thead>
                <tr>
                    <th class="w-8">
                        <input type="checkbox" @change="toggleAll($event)"
                               class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 cursor-pointer">
                    </th>
                    <th><a href="{{ $sortUrl('created_at') }}" class="hover:text-blue-700">Ticket #{{ $sortIcon('created_at') }}</a></th>
                    <th>Title</th><th>Requester</th><th>Dept</th>
                    <th>Category</th>
                    <th><a href="{{ $sortUrl('priority_id') }}" class="hover:text-blue-700">Priority{{ $sortIcon('priority_id') }}</a></th>
                    <th><a href="{{ $sortUrl('status') }}" class="hover:text-blue-700">Status{{ $sortIcon('status') }}</a></th>
                    <th><a href="{{ $sortUrl('sla_due_at') }}" class="hover:text-blue-700">SLA{{ $sortIcon('sla_due_at') }}</a></th>
                    <th>Assigned To</th>
                    <th><a href="{{ $sortUrl('created_at') }}" class="hover:text-blue-700">Date{{ $sortIcon('created_at') }}</a></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr class="{{ $ticket->sla_status==='breached' ? 'bg-red-50' : ($ticket->sla_status==='warning' ? 'bg-yellow-50':'') }}"
                    :class="selected.includes({{ $ticket->id }}) ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : ''">
                    <td class="w-8">
                        <input type="checkbox"
                               :value="{{ $ticket->id }}"
                               @change="toggle({{ $ticket->id }})"
                               :checked="selected.includes({{ $ticket->id }})"
                               class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 cursor-pointer">
                    </td>
                    <td class="font-mono text-xs font-semibold text-gray-600">{{ $ticket->ticket_number }}</td>
                    <td class="max-w-[150px]">
                        <a href="{{ route('admin.tickets.show', $ticket) }}"
                           class="text-blue-700 hover:underline font-medium text-sm line-clamp-1">{{ $ticket->title }}</a>
                    </td>
                    <td class="text-xs text-gray-600">{{ $ticket->requester->name }}</td>
                    <td class="text-xs text-gray-500">{{ Str::limit($ticket->department->name, 12) }}</td>
                    <td class="text-xs text-gray-500">{{ Str::limit($ticket->category->name, 14) }}</td>
                    <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                    <td><x-ticket-badge :status="$ticket->status"/></td>
                    <td><x-sla-indicator :ticket="$ticket"/></td>
                    <td class="text-xs text-gray-600">{{ $ticket->assignee?->name ?? '—' }}</td>
                    <td class="text-xs text-gray-400 whitespace-nowrap">{{ $ticket->created_at->format('M d, Y') }}</td>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn-secondary btn-sm mr-1">Open</a>
                        <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" class="inline"
                              onsubmit="return confirm('Delete ticket {{ $ticket->ticket_number }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center py-10 text-gray-400">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

@if($tickets->hasPages())
    <div class="mt-4">{{ $tickets->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
function bulkActions() {
    return {
        selected: [],
        pendingAction: '',
        pendingAssignTo: '',
        pendingPriority: '',
        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) this.selected.push(id);
            else this.selected.splice(idx, 1);
        },
        toggleAll(event) {
            this.selected = event.target.checked ? @json($tickets->pluck('id')) : [];
        },
        clearAll() { this.selected = []; },
        submitBulk(action, assignTo = null, priorityId = null) {
            if (!this.selected.length) return;
            const labels = { assign:'assign selected tickets', resolve:'mark as Resolved', close:'close selected tickets', priority:'change priority' };
            if (!confirm(`Are you sure you want to ${labels[action]}?\n\n${this.selected.length} ticket(s) will be affected and logged.`)) return;
            this.pendingAction = action;
            this.pendingAssignTo = assignTo ?? '';
            this.pendingPriority = priorityId ?? '';
            this.$nextTick(() => document.getElementById('adminBulkForm').submit());
        },
        confirmBulk(e) { e.preventDefault(); }
    }
}
</script>
@endpush
