<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
            class="relative p-2 rounded-lg hover:bg-blue-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @php $unread = auth()->user()->unreadNotifications->count(); @endphp
        @if ($unread > 0)
            <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold
                         rounded-full flex items-center justify-center">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" x-cloak
         class="absolute right-0 mt-1 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
            <span class="text-sm font-semibold text-gray-700">Notifications</span>
            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all read</button>
                </form>
            @endif
        </div>

        @php
            $notifications = auth()->user()->notifications()->latest()->limit(5)->get();
        @endphp

        @forelse ($notifications as $n)
            @php $data = $n->data; @endphp
            <a href="{{ route('notifications.read', $n->id) }}"
               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 text-sm
                      {{ $n->read_at ? 'opacity-60' : '' }}">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                    @if(($data['type'] ?? '') === 'urgent_alert')
                        <span class="text-base">⚠️</span>
                    @else
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-800 leading-snug">{{ $data['message'] ?? 'Notification' }}</p>
                    <p class="text-gray-400 text-xs mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                @unless($n->read_at)
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 shrink-0"></div>
                @endunless
            </a>
        @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">No notifications yet.</div>
        @endforelse

        @if ($notifications->count())
            <a href="{{ route('notifications.index') }}"
               class="block text-center text-xs text-blue-600 hover:underline py-2.5 bg-gray-50">
                View all notifications
            </a>
        @endif
    </div>
</div>
