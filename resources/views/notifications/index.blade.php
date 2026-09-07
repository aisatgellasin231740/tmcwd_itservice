@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button type="submit" class="btn-secondary btn-sm">Mark all as read</button>
    </form>
</div>

<div class="card max-w-2xl">
    <div class="divide-y divide-gray-100">
        @forelse($notifications as $n)
        @php $data = $n->data; $isUnread = is_null($n->read_at); @endphp
        <div class="flex items-start gap-4 px-6 py-4 {{ $isUnread ? 'bg-blue-50' : '' }} hover:bg-gray-50 transition">
            <div class="w-10 h-10 rounded-full {{ ($data['type'] ?? '') === 'urgent_alert' ? 'bg-red-100' : 'bg-blue-100' }}
                        flex items-center justify-center shrink-0 mt-0.5">
                @if(($data['type'] ?? '') === 'urgent_alert')
                    <span class="text-lg">⚠️</span>
                @elseif(($data['type'] ?? '') === 'ticket_assigned')
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                @elseif(($data['type'] ?? '') === 'ticket_commented')
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800 {{ $isUnread ? 'font-semibold' : '' }}">
                    {{ $data['message'] ?? 'Notification' }}
                </p>
                @if(isset($data['ticket_number']))
                <p class="text-xs text-gray-500 mt-0.5">Ticket: {{ $data['ticket_number'] }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($isUnread)
                    <div class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                @endif
                <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-blue-600">
                        {{ $isUnread ? 'Mark read' : '✓' }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-gray-400">No notifications yet.</p>
        </div>
        @endforelse
    </div>
</div>

@if($notifications->hasPages())
    <div class="mt-4 max-w-2xl">{{ $notifications->links() }}</div>
@endif
@endsection
