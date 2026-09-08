<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Prevent dark mode flash on load --}}
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-size: 13px; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 500;
            color: #4b5563;
            transition: all 0.15s;
            text-decoration: none;
            white-space: nowrap;
        }
        .nav-item:hover { background: #eff6ff; color: #1d4ed8; }
        .nav-item.active { background: #1d4ed8; color: #fff; }
        .nav-item.active svg { color: #fff; }
        .nav-section {
            padding: 12px 10px 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #9ca3af;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800"
      x-data="{ sidebarOpen: false }"
      x-init="
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
      ">

{{-- ── TOP BAR (h-16, clear and readable) ────────────────────────────── --}}
<header class="fixed top-0 left-0 right-0 z-30 h-16 flex items-center px-4 gap-3 shadow-lg"
        style="background: linear-gradient(90deg, #1e3a8a 0%, #1d4ed8 60%, #1e40af 100%);">

    {{-- Mobile toggle --}}
    <button @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-lg text-white/80 hover:bg-white/10 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Logo + Organization name --}}
    <div class="flex items-center gap-3 flex-shrink-0">
        {{-- Logo circle --}}
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
            <img src="{{ asset('images/tmcwd-logo.png') }}" alt="TMCWD"
                 class="w-9 h-9 object-contain rounded-lg"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div style="display:none"
                 class="w-10 h-10 bg-blue-700 rounded-xl items-center justify-center">
                <span class="text-white font-bold text-xs">IT</span>
            </div>
        </div>
        {{-- Text --}}
        <div class="hidden sm:block">
            <p class="text-white font-bold text-sm leading-tight tracking-wide">
                TMCWD IT Request Service
            </p>
            <p class="text-blue-200 text-[11px] leading-tight font-medium">
                Trece Martires City Water District
            </p>
        </div>
    </div>

    {{-- Thin vertical divider --}}
    <div class="hidden sm:block w-px h-8 bg-white/20 mx-1"></div>

    {{-- Search bar --}}
    @hasanyrole('it_staff|it_head')
    <div class="flex-1 max-w-xs hidden md:block">
        <form method="GET" action="{{ route('search') }}">
            <div class="relative">
                <svg class="w-4 h-4 text-blue-300 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search tickets…"
                       class="w-full pl-9 pr-4 py-2 text-sm bg-white/10 border border-white/20
                              rounded-xl text-white placeholder-blue-300 focus:outline-none
                              focus:ring-2 focus:ring-white/30 focus:bg-white/20 transition">
            </div>
        </form>
    </div>
    @else
    <div class="flex-1"></div>
    @endhasanyrole

    {{-- Right side --}}
    <div class="ml-auto flex items-center gap-2">

        {{-- Dark Mode Toggle --}}
        <button @click="
                const html = document.documentElement;
                html.classList.toggle('dark');
                localStorage.setItem('darkMode', html.classList.contains('dark'));
            "
            class="p-2 rounded-lg text-white/70 hover:bg-white/10 transition"
            title="Toggle dark mode">
            {{-- Sun icon (show in dark mode) --}}
            <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
            </svg>
            {{-- Moon icon (show in light mode) --}}
            <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        {{-- Notification Bell --}}
        <x-notification-bell />

        {{-- Divider --}}
        <div class="w-px h-7 bg-white/20"></div>

        {{-- User dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-white/10 transition">
                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-xl bg-white/20 border border-white/30
                            flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                    {{ auth()->user()->initials }}
                </div>
                {{-- Name + Role --}}
                <div class="hidden sm:block text-left">
                    <p class="text-white text-sm font-semibold leading-tight truncate max-w-[110px]">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-blue-200 text-[11px] leading-tight">
                        @php $r = auth()->user()->roles->first()?->name ?? '' @endphp
                        {{ match($r) { 'it_head' => 'IT Head', 'it_staff' => 'IT Staff', default => 'Employee' } }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-blue-300 hidden sm:block"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="open" @click.away="open = false" x-cloak x-transition
                 class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                {{-- Header --}}
                <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center
                                    text-white font-bold text-sm flex-shrink-0">
                            {{ auth()->user()->initials }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-blue-200 text-[10px] truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
                {{-- Menu items --}}
                <div class="py-1.5">
                    <a href="{{ route('profile.show') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        My Profile
                    </a>
                    <a href="{{ route('notifications.index') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                        <svg class="w-4 h-4 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notifications
                        @php $uc = auth()->user()->unreadNotifications->count() @endphp
                        @if($uc > 0)
                            <span class="ml-auto bg-red-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5">
                                {{ $uc }}
                            </span>
                        @endif
                    </a>
                </div>
                <div class="border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600
                                       hover:bg-red-50 transition">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
{{-- Mobile overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
     class="fixed inset-0 bg-black/40 z-20 lg:hidden"></div>

{{-- ── SIDEBAR (compact w-52) ──────────────────────────────────────────── --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed top-16 left-0 z-20 h-[calc(100vh-4rem)] w-52 bg-white border-r border-gray-200
              transform transition-transform duration-200 lg:translate-x-0
              overflow-y-auto flex flex-col shadow-sm">

    <nav class="flex-1 px-2 py-3 space-y-0.5">

        {{-- ── Requester ── --}}
        @role('requester')
        <p class="nav-section">My Workspace</p>
        <a href="{{ route('requester.dashboard') }}"
           class="nav-item {{ request()->routeIs('requester.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
        <a href="{{ route('requester.tickets.index') }}"
           class="nav-item {{ request()->routeIs('requester.tickets.index') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            My Tickets
        </a>
        <a href="{{ route('requester.tickets.create') }}"
           class="nav-item {{ request()->routeIs('requester.tickets.create') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + New Request
        </a>
        @endrole

        {{-- ── IT Staff ── --}}
        @role('it_staff')
        <p class="nav-section">IT Support</p>
        <a href="{{ route('agent.dashboard') }}"
           class="nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
        <a href="{{ route('agent.tickets.index') }}"
           class="nav-item {{ request()->routeIs('agent.tickets.index') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Ticket Queue
        </a>
        <a href="{{ route('agent.tickets.index', ['assignee' => 'me']) }}"
           class="nav-item {{ request()->is('agent/tickets*') && request('assignee')==='me' ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            My Assignments
        </a>
        <a href="{{ route('search') }}"
           class="nav-item {{ request()->routeIs('search') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Search
        </a>
        @endrole

        {{-- ── IT Head ── --}}
        @role('it_head')
        <p class="nav-section">Overview</p>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <p class="nav-section">Tickets</p>
        <a href="{{ route('admin.tickets.index') }}"
           class="nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            All Tickets
        </a>
        <a href="{{ route('agent.tickets.index') }}"
           class="nav-item {{ request()->routeIs('agent.tickets.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            IT Staff Queue
        </a>

        <p class="nav-section">Management</p>
        <a href="{{ route('admin.users.index') }}"
           class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Users
        </a>
        <a href="{{ route('admin.departments.index') }}"
           class="nav-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Departments
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Categories
        </a>
        <a href="{{ route('admin.priorities.index') }}"
           class="nav-item {{ request()->routeIs('admin.priorities.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
            </svg>
            Priorities / SLA
        </a>

        <p class="nav-section">Analytics</p>
        <a href="{{ route('admin.reports.index') }}"
           class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Reports
        </a>
        <a href="{{ route('admin.activity.index') }}"
           class="nav-item {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Activity Log
        </a>
        <a href="{{ route('search') }}"
           class="nav-item {{ request()->routeIs('search') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Search
        </a>
        @endrole
    </nav>

    {{-- Sidebar footer: user mini card --}}
    <div class="border-t border-gray-100 p-2">
        <a href="{{ route('profile.show') }}"
           class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-gray-50 transition group">
            <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center
                        text-white text-[10px] font-bold shrink-0">
                {{ auth()->user()->initials }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-gray-700 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
            <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-500 shrink-0"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</aside>

{{-- ── MAIN CONTENT ────────────────────────────────────────────────────── --}}
<main class="lg:ml-52 pt-16 min-h-screen">
    <div class="p-4 lg:p-5 pb-24 lg:pb-5">{{-- pb-24 on mobile = space for bottom nav --}}

        {{-- Flash success --}}
        @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 flex items-center gap-2.5 px-3 py-2.5 bg-green-50 border border-green-200
                    text-green-800 rounded-xl text-xs shadow-sm">
            <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="show = false" class="text-green-400 hover:text-green-600 ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Flash error --}}
        @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="mb-4 flex items-center gap-2.5 px-3 py-2.5 bg-red-50 border border-red-200
                    text-red-800 rounded-xl text-xs shadow-sm">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="flex-1">{{ session('error') }}</span>
            <button @click="show = false" class="text-red-400 hover:text-red-600 ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @yield('content')
    </div>
</main>

@stack('scripts')
<script>
function globalSearch() { return { open: false }; }
</script>

{{-- ── MOBILE BOTTOM NAV (visible only on small screens, hidden on lg+) ── --}}
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white dark:bg-gray-900
            border-t border-gray-200 dark:border-gray-700 shadow-lg safe-bottom">
    <div class="flex items-center justify-around px-2 py-1.5">

        {{-- Requester bottom nav --}}
        @role('requester')
        <a href="{{ route('requester.dashboard') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('requester.dashboard') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('requester.dashboard') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Home</span>
        </a>

        <a href="{{ route('requester.tickets.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('requester.tickets.index') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('requester.tickets.index') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">My Tickets</span>
        </a>

        <a href="{{ route('requester.tickets.create') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('requester.tickets.create') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center shadow-md -mt-5">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span class="text-[10px] font-medium leading-none mt-0.5 text-blue-600">New</span>
        </a>

        <a href="{{ route('notifications.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('notifications.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition relative">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('notifications.*') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @php $unreadMobile = auth()->user()->unreadNotifications->count(); @endphp
            @if($unreadMobile > 0)
                <span class="absolute top-1 right-2.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold
                             rounded-full flex items-center justify-center">{{ $unreadMobile > 9 ? '9+' : $unreadMobile }}</span>
            @endif
            <span class="text-[10px] font-medium leading-none">Alerts</span>
        </a>

        <a href="{{ route('profile.show') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold
                        {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white' : 'text-blue-600' }}">
                {{ auth()->user()->initials }}
            </div>
            <span class="text-[10px] font-medium leading-none mt-0.5">Profile</span>
        </a>
        @endrole

        {{-- IT Staff bottom nav --}}
        @role('it_staff')
        <a href="{{ route('agent.dashboard') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('agent.dashboard') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('agent.dashboard') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Home</span>
        </a>

        <a href="{{ route('agent.tickets.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('agent.tickets.index') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('agent.tickets.index') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Queue</span>
        </a>

        <a href="{{ route('agent.tickets.index', ['assignee' => 'me']) }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->is('agent/tickets*') && request('assignee') === 'me' ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Mine</span>
        </a>

        <a href="{{ route('search') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('search') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Search</span>
        </a>

        <a href="{{ route('profile.show') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold
                        {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white' : 'text-blue-600' }}">
                {{ auth()->user()->initials }}
            </div>
            <span class="text-[10px] font-medium leading-none mt-0.5">Profile</span>
        </a>
        @endrole

        {{-- IT Head bottom nav --}}
        @role('it_head')
        <a href="{{ route('admin.dashboard') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('admin.dashboard') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Home</span>
        </a>

        <a href="{{ route('admin.tickets.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('admin.tickets.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('admin.tickets.*') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Tickets</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('admin.users.*') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Users</span>
        </a>

        <a href="{{ route('admin.reports.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('admin.reports.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('admin.reports.*') ? 'currentColor' : 'none' }}"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-[10px] font-medium leading-none">Reports</span>
        </a>

        <a href="{{ route('profile.show') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl min-w-[56px]
                  {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-gray-500' }}
                  hover:text-blue-600 transition">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold
                        {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white' : 'text-blue-600' }}">
                {{ auth()->user()->initials }}
            </div>
            <span class="text-[10px] font-medium leading-none mt-0.5">Profile</span>
        </a>
        @endrole

    </div>
</nav>
</body>
</html>
