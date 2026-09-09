@extends('layouts.app')
@section('title', 'My Dashboard')

@section('content')
@php
    $hour      = now()->hour;
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = str(auth()->user()->name)->before(' ')->toString();
@endphp
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $greeting }}, {{ $firstName }}</p>
    </div>
    <a href="{{ route('requester.tickets.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Request
    </a>
</div>

{{-- ── Stat cards ──────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-3">

    <x-stat-card
        label="Total Requests"
        :value="$stats['total']"
        color="blue"
        :trend="$trends['total']"
        :active="$filter === 'all'"
        :href="route('requester.dashboard', ['filter' => 'all'])"
    />

    <x-stat-card
        label="Open / Active"
        :value="$stats['open']"
        color="yellow"
        :trend="$trends['open']"
        :active="$filter === 'open'"
        :href="route('requester.dashboard', ['filter' => 'open'])"
    />

    <x-stat-card
        label="Resolved"
        :value="$stats['resolved']"
        color="green"
        :trend="$trends['resolved']"
        :active="$filter === 'resolved'"
        :href="route('requester.dashboard', ['filter' => 'resolved'])"
    />

    <x-stat-card
        label="Closed"
        :value="$stats['closed']"
        color="gray"
        :trend="$trends['closed']"
        :active="$filter === 'closed'"
        :href="route('requester.dashboard', ['filter' => 'closed'])"
    />

</div>

{{-- ── Active-filter pill + reset ─────────────────────────────────────── --}}
@if($filter !== 'all')
<div class="mb-5 flex items-center gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-medium text-blue-700">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        Filtered: {{ match($filter) {
            'open'     => 'Open / Active',
            'resolved' => 'Resolved',
            'closed'   => 'Closed',
            default    => ucfirst($filter),
        } }}
    </span>
    <a href="{{ route('requester.dashboard') }}"
       class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 hover:underline transition-colors">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Show all
    </a>
</div>
@else
<div class="mb-5"></div>
@endif

{{-- ── Recent Requests table ───────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-800">
            @if($filter === 'all')
                Recent Requests
            @else
                Recent Requests &mdash;
                <span class="font-normal text-gray-500">{{ match($filter) {
                    'open'     => 'Open / Active',
                    'resolved' => 'Resolved',
                    'closed'   => 'Closed',
                    default    => ucfirst($filter),
                } }}</span>
            @endif
        </h2>
        <a href="{{ route('requester.tickets.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
    </div>

    <div class="table-wrapper rounded-none border-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTickets as $ticket)
                @php
                    $priorityName = strtolower($ticket->priority->name);
                    $priorityRowClass = match(true) {
                        str_contains($priorityName, 'urgent') => 'priority-row-urgent',
                        str_contains($priorityName, 'high')   => 'priority-row-high',
                        str_contains($priorityName, 'medium') => 'priority-row-medium',
                        default                               => '',
                    };
                @endphp
                <tr class="{{ $priorityRowClass }}">
                    <td>
                        <a href="{{ route('requester.tickets.show', $ticket) }}"
                           class="font-mono text-blue-600 hover:underline font-medium">
                            {{ $ticket->ticket_number }}
                        </a>
                    </td>
                    <td class="max-w-xs truncate">{{ $ticket->title }}</td>
                    <td class="text-gray-500">{{ $ticket->category->name }}</td>
                    <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                    <td><x-ticket-badge :status="$ticket->status"/></td>
                    <td class="text-gray-400 text-xs">{{ $ticket->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-400">
                        @if($filter === 'all')
                            No requests yet.
                            <a href="{{ route('requester.tickets.create') }}" class="text-blue-600 hover:underline">Submit your first request →</a>
                        @else
                            No {{ match($filter) {
                                'open'     => 'open or active',
                                'resolved' => 'resolved',
                                'closed'   => 'closed',
                                default    => $filter,
                            } }} requests.
                            <a href="{{ route('requester.dashboard') }}" class="text-blue-600 hover:underline">Show all →</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
