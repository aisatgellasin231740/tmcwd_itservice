@extends('layouts.app')
@section('title', 'Search Results')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Search Results</h1>
    @if($query)
        <p class="text-sm text-gray-500 mt-1">
            Results for <span class="font-semibold text-gray-700">"{{ $query }}"</span>
            @isset($tickets)
                &nbsp;·&nbsp; {{ $tickets->count() }} ticket{{ $tickets->count() !== 1 ? 's' : '' }} found
            @endisset
        </p>
    @endif
</div>

{{-- Inline search bar on results page --}}
<div class="card mb-6 max-w-xl">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('search') }}" class="flex gap-2">
            <input type="text" name="q" value="{{ $query }}"
                   placeholder="Search tickets…"
                   class="form-input flex-1"
                   autofocus>
            <button type="submit" class="btn-primary shrink-0">Search</button>
        </form>
    </div>
</div>

@if(isset($tooShort) && $tooShort)
    <div class="card max-w-xl">
        <div class="card-body text-center py-10 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="font-medium text-gray-600">Enter at least 2 characters to search.</p>
        </div>
    </div>
@elseif($tickets->isEmpty())
    <div class="card max-w-xl">
        <div class="card-body text-center py-10">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="font-medium text-gray-600">No tickets found for "{{ $query }}"</p>
            <p class="text-sm text-gray-400 mt-1">Try searching by ticket number, title, or requester name.</p>
        </div>
    </div>
@else
    <div class="table-wrapper max-w-5xl">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                @php
                    // Highlight matched text helper
                    $hl = fn(string $text) => preg_replace(
                        '/(' . preg_quote(e(request('q')), '/') . ')/iu',
                        '<mark class="bg-yellow-200 rounded px-0.5">$1</mark>',
                        e($text)
                    );
                @endphp
                <tr>
                    <td>
                        <a href="{{ auth()->user()->hasAnyRole(['it_staff','it_head'])
                                    ? (auth()->user()->hasRole('it_head') ? route('admin.tickets.show', $ticket) : route('agent.tickets.show', $ticket))
                                    : route('requester.tickets.show', $ticket) }}"
                           class="font-mono text-xs font-semibold text-blue-600 hover:underline">
                            {!! $hl($ticket->ticket_number) !!}
                        </a>
                    </td>
                    <td class="max-w-[200px]">
                        <a href="{{ auth()->user()->hasAnyRole(['it_staff','it_head'])
                                    ? (auth()->user()->hasRole('it_head') ? route('admin.tickets.show', $ticket) : route('agent.tickets.show', $ticket))
                                    : route('requester.tickets.show', $ticket) }}"
                           class="text-blue-700 hover:underline font-medium text-sm">
                            {!! $hl($ticket->title) !!}
                        </a>
                    </td>
                    <td class="text-xs text-gray-600">{!! $hl($ticket->requester->name) !!}</td>
                    <td class="text-xs text-gray-500">{{ Str::limit($ticket->department->name, 16) }}</td>
                    <td class="text-xs text-gray-500">{{ Str::limit($ticket->category->name, 18) }}</td>
                    <td><x-ticket-badge :priority="$ticket->priority->name"/></td>
                    <td><x-ticket-badge :status="$ticket->status"/></td>
                    <td class="text-xs text-gray-400 whitespace-nowrap">{{ $ticket->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ auth()->user()->hasAnyRole(['it_staff','it_head'])
                                    ? (auth()->user()->hasRole('it_head') ? route('admin.tickets.show', $ticket) : route('agent.tickets.show', $ticket))
                                    : route('requester.tickets.show', $ticket) }}"
                           class="btn-secondary btn-sm">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($tickets->count() === 30)
        <p class="text-xs text-gray-400 mt-2 max-w-5xl text-right">Showing top 30 results. Refine your search for more specific results.</p>
    @endif
@endif
@endsection
