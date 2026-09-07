@extends('layouts.app')
@section('title', 'My Dashboard')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Welcome back, {{ auth()->user()->name }}</p>
    </div>
    <a href="{{ route('requester.tickets.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Request
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card label="Total Requests" :value="$stats['total']" color="blue"/>
    <x-stat-card label="Open / Active"  :value="$stats['open']"  color="yellow"/>
    <x-stat-card label="Resolved"       :value="$stats['resolved']" color="green"/>
    <x-stat-card label="Closed"         :value="$stats['closed']"   color="gray"/>
</div>

{{-- Recent Tickets --}}
<div class="card">
    <div class="card-header flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-800">Recent Requests</h2>
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
                <tr>
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
                        No requests yet.
                        <a href="{{ route('requester.tickets.create') }}" class="text-blue-600 hover:underline">Submit your first request →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
