@extends('layouts.app')
@section('title', 'Ticket Queue')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Ticket Queue</h1>
</div>

{{-- Filters --}}
<div class="card mb-5">
    <div class="card-body">
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
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select w-44">
                    <option value="">All</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
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
                <label class="form-label">Assignee</label>
                <select name="assignee" class="form-select w-36">
                    <option value="">All</option>
                    <option value="me"         {{ request('assignee')==='me'?'selected':'' }}>Assigned to Me</option>
                    <option value="unassigned" {{ request('assignee')==='unassigned'?'selected':'' }}>Unassigned</option>
                </select>
            </div>
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket # or title" class="form-input w-40">
            </div>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['status','priority_id','category_id','department_id','assignee','search']))
                <a href="{{ route('agent.tickets.index') }}" class="btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
{{-- Table --}}
<div class="table-wrapper">
    <table class="data-table">
        @php
            $sort = request('sort', 'created_at');
            $dir  = request('dir', 'desc');
            $toggleDir = $dir === 'asc' ? 'desc' : 'asc';
            $sortUrl = fn(string $field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $sort === $field ? $toggleDir : 'desc', 'page' => 1]);
            $sortIcon = fn(string $field) => $sort === $field
                ? ($dir === 'asc' ? ' ↑' : ' ↓')
                : ' ↕';
        @endphp
        <thead>
            <tr>
                <th><a href="{{ $sortUrl('created_at') }}" class="hover:text-blue-700">Ticket #{{ $sortIcon('created_at') }}</a></th>
                <th>Title</th>
                <th>Requester</th>
                <th>Dept</th>
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
            <tr class="{{ $ticket->sla_status === 'breached' ? 'bg-red-50' : ($ticket->sla_status === 'warning' ? 'bg-yellow-50' : '') }}">
                <td class="font-mono text-xs font-semibold text-gray-600">{{ $ticket->ticket_number }}</td>
                <td class="max-w-[200px]">
                    <a href="{{ route('agent.tickets.show', $ticket) }}"
                       class="text-blue-700 hover:underline font-medium line-clamp-2 text-sm">{{ $ticket->title }}</a>
                </td>
                <td class="text-xs text-gray-600">{{ $ticket->requester->name }}</td>
                <td class="text-xs text-gray-500">{{ Str::limit($ticket->department->name, 12) }}</td>
                <td class="text-xs text-gray-500">{{ Str::limit($ticket->category->name, 18) }}</td>
                <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                <td><x-ticket-badge :status="$ticket->status"/></td>
                <td><x-sla-indicator :ticket="$ticket"/></td>
                <td class="text-xs text-gray-600">{{ $ticket->assignee?->name ?? '—' }}</td>
                <td class="text-xs text-gray-400 whitespace-nowrap">{{ $ticket->created_at->format('M d, Y') }}</td>
                <td>
                    <a href="{{ route('agent.tickets.show', $ticket) }}"
                       class="btn-secondary btn-sm">Open</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="11" class="text-center py-10 text-gray-400">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($tickets->hasPages())
    <div class="mt-4">{{ $tickets->links() }}</div>
@endif
@endsection
