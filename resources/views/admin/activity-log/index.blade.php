@extends('layouts.app')
@section('title', 'Activity Log')

@section('content')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
        <p class="text-sm text-gray-500 mt-0.5">Full audit trail of all ticket actions across the system.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.activity.csv', request()->query()) }}" class="btn-secondary btn-sm">
            Export CSV
        </a>
        <a href="{{ route('admin.activity.pdf', request()->query()) }}" class="btn-primary btn-sm">
            Export PDF
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-5">
    <div class="card-body py-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end" data-auto-filter>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Ticket</label>
                <select name="ticket_id" class="form-select w-52">
                    <option value="">All Tickets</option>
                    @foreach($tickets as $t)
                        <option value="{{ $t->id }}" {{ $ticketId == $t->id ? 'selected' : '' }}>
                            {{ $t->ticket_number }} — {{ Str::limit($t->title, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['date_from','date_to','ticket_id']))
                <a href="{{ route('admin.activity.index') }}" class="btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Summary --}}
<div class="mb-3 text-sm text-gray-500">
    Showing <span class="font-medium text-gray-700">{{ $activities->total() }}</span> log entries
    from <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }}</span>
    to <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}</span>
</div>

{{-- Table --}}
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Ticket #</th>
                <th>Action</th>
                <th>Description</th>
                <th>Old Value</th>
                <th>New Value</th>
                <th>Performed By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $act)
            <tr>
                <td class="text-xs text-gray-500 whitespace-nowrap">
                    {{ $act->created_at->format('M d, Y') }}<br>
                    <span class="text-gray-400">{{ $act->created_at->format('g:i A') }}</span>
                </td>
                <td>
                    @if($act->ticket)
                    <a href="{{ route('admin.tickets.show', $act->ticket) }}"
                       class="font-mono text-xs font-semibold text-blue-600 hover:underline">
                        {{ $act->ticket->ticket_number }}
                    </a>
                    @else
                        <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                <td>
                    @php
                        $actionColors = [
                            'created'          => 'bg-blue-100 text-blue-700',
                            'status_changed'   => 'bg-yellow-100 text-yellow-700',
                            'assigned'         => 'bg-purple-100 text-purple-700',
                            'priority_changed' => 'bg-orange-100 text-orange-700',
                            'comment_added'    => 'bg-green-100 text-green-700',
                            'attachment_added' => 'bg-gray-100 text-gray-700',
                        ];
                        $cls = $actionColors[$act->action] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <span class="badge {{ $cls }} text-xs">
                        {{ str_replace('_', ' ', ucfirst($act->action)) }}
                    </span>
                </td>
                <td class="text-xs text-gray-700 max-w-[200px]">
                    {{ $act->description ?? $act->description_label }}
                </td>
                <td class="text-xs text-gray-500">{{ $act->old_value ?? '—' }}</td>
                <td class="text-xs text-gray-500">{{ $act->new_value ?? '—' }}</td>
                <td class="text-xs text-gray-600 whitespace-nowrap">
                    {{ $act->user?->name ?? 'System' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-10 text-gray-400">No activity found for this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($activities->hasPages())
    <div class="mt-4">{{ $activities->links() }}</div>
@endif
@endsection
