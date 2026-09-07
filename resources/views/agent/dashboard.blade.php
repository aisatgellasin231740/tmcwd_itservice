@extends('layouts.app')
@section('title', 'Agent Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">IT Support Dashboard</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F j, Y') }}</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <x-stat-card label="Assigned to Me"   :value="$stats['assigned_open']"   color="blue"/>
    <x-stat-card label="In Progress"      :value="$stats['in_progress']"     color="yellow"/>
    <x-stat-card label="Resolved Today"   :value="$stats['resolved_today']"  color="green"/>
    <x-stat-card label="SLA Breached"     :value="$stats['sla_breached']"    color="red"/>
    <x-stat-card label="Unassigned Open"  :value="$stats['unassigned_open']" color="orange"/>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Unassigned Queue --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Unassigned Queue</h2>
            <a href="{{ route('agent.tickets.index', ['assignee'=>'unassigned','status'=>'open']) }}"
               class="text-sm text-blue-600 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr>
                    <th>Ticket</th><th>Priority</th><th>Dept</th><th>SLA</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($unassignedQueue as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('agent.tickets.show', $ticket) }}" class="text-blue-600 hover:underline font-medium text-xs font-mono">{{ $ticket->ticket_number }}</a>
                            <p class="text-xs text-gray-500 truncate max-w-[160px]">{{ $ticket->title }}</p>
                        </td>
                        <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                        <td class="text-xs text-gray-500">{{ Str::limit($ticket->department->name, 15) }}</td>
                        <td><x-sla-indicator :ticket="$ticket"/></td>
                        <td>
                            <form method="POST" action="{{ route('agent.tickets.update', $ticket) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                                <input type="hidden" name="_from" value="dashboard">
                                <button type="submit" class="btn-primary btn-sm">Assign to Me</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-6 text-gray-400 text-sm">No unassigned tickets 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- My Active Tickets --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">My Active Tickets</h2>
            <a href="{{ route('agent.tickets.index', ['assignee'=>'me']) }}"
               class="text-sm text-blue-600 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr>
                    <th>Ticket</th><th>Priority</th><th>Status</th><th>SLA</th>
                </tr></thead>
                <tbody>
                    @forelse($myTickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('agent.tickets.show', $ticket) }}" class="text-blue-600 hover:underline font-mono text-xs font-medium">{{ $ticket->ticket_number }}</a>
                            <p class="text-xs text-gray-500 truncate max-w-[160px]">{{ $ticket->title }}</p>
                        </td>
                        <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                        <td><x-ticket-badge :status="$ticket->status"/></td>
                        <td><x-sla-indicator :ticket="$ticket"/></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-6 text-gray-400 text-sm">No active tickets assigned to you.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
