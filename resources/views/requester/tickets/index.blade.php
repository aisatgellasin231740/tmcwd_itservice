@extends('layouts.app')
@section('title', 'My Tickets')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">My Tickets</h1>
    <a href="{{ route('requester.tickets.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Request
    </a>
</div>

{{-- Filters --}}
<div class="card mb-5">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end" data-auto-filter>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-36">
                    <option value="">All Statuses</option>
                    @foreach(['open'=>'Open','in_progress'=>'In Progress','on_hold'=>'On Hold','resolved'=>'Resolved','closed'=>'Closed'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-36">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-36">
            </div>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['status','date_from','date_to']))
                <a href="{{ route('requester.tickets.index') }}" class="btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Submitted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr>
                <td>
                    <span class="font-mono text-xs font-medium text-gray-600">{{ $ticket->ticket_number }}</span>
                </td>
                <td class="max-w-xs">
                    <a href="{{ route('requester.tickets.show', $ticket) }}"
                       class="text-blue-700 hover:underline font-medium line-clamp-1">
                        {{ $ticket->title }}
                    </a>
                </td>
                <td class="text-gray-500 text-xs">{{ $ticket->category->name }}</td>
                <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                <td><x-ticket-badge :status="$ticket->status"/></td>
                <td class="text-gray-400 text-xs whitespace-nowrap">{{ $ticket->created_at->format('M d, Y') }}</td>
                <td>
                    <a href="{{ route('requester.tickets.show', $ticket) }}" class="text-blue-600 hover:underline text-xs">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-10 text-gray-400">No tickets found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tickets->hasPages())
    <div class="mt-4">{{ $tickets->links() }}</div>
@endif
@endsection
