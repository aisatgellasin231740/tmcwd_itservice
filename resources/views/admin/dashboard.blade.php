@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')

{{-- ── Greeting bar ──────────────────────────────────────────────────────── --}}
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        @endphp
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ $greeting }}, {{ auth()->user()->name }}
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F j, Y') }}</p>

        {{-- Urgent alert banner --}}
        @if($stats['sla_breached'] > 0 || $stats['unassigned'] > 0)
        <div class="mt-2 flex flex-wrap gap-2">
            @if($stats['sla_breached'] > 0)
            <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $stats['sla_breached'] }} SLA breach{{ $stats['sla_breached'] > 1 ? 'es' : '' }} — needs attention
            </a>
            @endif
            @if($stats['unassigned'] > 0)
            <a href="{{ route('admin.tickets.index', ['assigned_to' => '']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-100 text-orange-700 rounded-lg text-xs font-semibold hover:bg-orange-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ $stats['unassigned'] }} unassigned ticket{{ $stats['unassigned'] > 1 ? 's' : '' }}
            </a>
            @endif
        </div>
        @else
        <p class="mt-2 text-xs text-green-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            All clear — no SLA breaches or unassigned tickets
        </p>
        @endif
    </div>

    {{-- Period filter --}}
    <div class="flex flex-wrap items-center gap-2" x-data="{ showCustom: {{ $period === 'custom' ? 'true' : 'false' }} }">
        @foreach(['this_week' => 'This Week', 'month' => 'This Month', 'quarter' => 'Quarter', 'year' => 'This Year', 'custom' => 'Custom'] as $key => $label)
            @if($key === 'custom')
                <button @click="showCustom = !showCustom"
                        class="text-xs px-3 py-1.5 rounded-lg border font-medium transition
                               {{ $period === 'custom' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
                    {{ $label }}
                </button>
            @else
                <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
                   class="text-xs px-3 py-1.5 rounded-lg border font-medium transition
                          {{ $period === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
                    {{ $label }}
                </a>
            @endif
        @endforeach

        <div x-show="showCustom" x-cloak class="flex items-center gap-2 w-full sm:w-auto mt-1 sm:mt-0">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2 items-end flex-wrap" data-auto-filter>
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="form-label text-xs">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input text-xs py-1.5">
                </div>
                <div>
                    <label class="form-label text-xs">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input text-xs py-1.5">
                </div>
                <button type="submit" class="btn-primary btn-sm">Apply</button>
            </form>
        </div>
    </div>
</div>

{{-- Active range label --}}
<p class="text-xs text-gray-400 mb-5">
    Showing:
    <span class="font-medium text-gray-600 dark:text-gray-300">
        {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}
    </span>
    &nbsp;·&nbsp;
    <a href="{{ route('admin.dashboard') }}" class="text-blue-500 hover:underline">Reset</a>
</p>

{{-- ── Stat cards row ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">

    {{-- Total Open --}}
    <div class="card border-l-4 border-l-blue-500 col-span-1">
        <div class="card-body py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Open</p>
            <p class="text-3xl font-bold text-blue-700 mt-0.5">{{ $stats['total_open'] }}</p>
            <p class="text-xs text-gray-400 mt-1">active tickets</p>
        </div>
    </div>

    {{-- Resolved (period) --}}
    <div class="card border-l-4 border-l-green-500 col-span-1">
        <div class="card-body py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Resolved</p>
            <p class="text-3xl font-bold text-green-700 mt-0.5">{{ $stats['resolved_range'] }}</p>
            @php $trend = $stats['resolved_trend']; @endphp
            <p class="text-xs mt-1 {{ $trend >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $trend >= 0 ? '↑' : '↓' }} {{ abs($trend) }} vs prev period
            </p>
        </div>
    </div>

    {{-- SLA Breaches --}}
    <div class="card border-l-4 {{ $stats['sla_breached'] > 0 ? 'border-l-red-500' : 'border-l-gray-300' }} col-span-1">
        <div class="card-body py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">SLA Breaches</p>
            <p class="text-3xl font-bold {{ $stats['sla_breached'] > 0 ? 'text-red-600' : 'text-gray-400' }} mt-0.5">
                {{ $stats['sla_breached'] }}
            </p>
            <p class="text-xs mt-1 {{ $stats['sla_breached'] > 0 ? 'text-red-400' : 'text-gray-400' }}">
                {{ $stats['sla_breached'] > 0 ? 'needs attention' : 'all good' }}
            </p>
        </div>
    </div>

    {{-- Avg Resolution --}}
    <div class="card border-l-4 border-l-yellow-500 col-span-1">
        <div class="card-body py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Avg Resolution</p>
            <p class="text-3xl font-bold text-yellow-700 mt-0.5">{{ $stats['avg_resolution'] }}<span class="text-base font-normal text-gray-400 ml-1">hrs</span></p>
            <p class="text-xs text-gray-400 mt-1">this period</p>
        </div>
    </div>

    {{-- Unassigned --}}
    <div class="card border-l-4 {{ $stats['unassigned'] > 0 ? 'border-l-orange-500' : 'border-l-gray-300' }} col-span-1">
        <div class="card-body py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Unassigned</p>
            <p class="text-3xl font-bold {{ $stats['unassigned'] > 0 ? 'text-orange-600' : 'text-gray-400' }} mt-0.5">
                {{ $stats['unassigned'] }}
            </p>
            <p class="text-xs text-gray-400 mt-1">open tickets</p>
        </div>
    </div>

    {{-- SLA Health --}}
    <div class="card border-l-4 {{ $stats['sla_health_pct'] >= 80 ? 'border-l-green-500' : ($stats['sla_health_pct'] >= 60 ? 'border-l-yellow-500' : 'border-l-red-500') }} col-span-1">
        <div class="card-body py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">SLA Health</p>
            <p class="text-3xl font-bold {{ $stats['sla_health_pct'] >= 80 ? 'text-green-700' : ($stats['sla_health_pct'] >= 60 ? 'text-yellow-700' : 'text-red-600') }} mt-0.5">
                {{ $stats['sla_health_pct'] }}<span class="text-base font-normal text-gray-400 ml-0.5">%</span>
            </p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1.5">
                <div class="h-1.5 rounded-full {{ $stats['sla_health_pct'] >= 80 ? 'bg-green-500' : ($stats['sla_health_pct'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $stats['sla_health_pct'] }}%"></div>
            </div>
        </div>
    </div>

</div>

{{-- ── Charts row ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Horizontal bar: By Department --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tickets by Department</h2>
            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">Period</span>
        </div>
        <div class="card-body">
            <canvas id="deptChart" height="260"></canvas>
        </div>
    </div>

    {{-- Doughnut: By Category --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tickets by Category</h2>
            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">Period</span>
        </div>
        <div class="card-body flex items-center justify-center">
            <canvas id="catChart" height="260"></canvas>
        </div>
    </div>

</div>

{{-- ── Bottom row: Status + Staff + Recent activity ─────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Status summary --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">By Status</h2>
            <span class="text-xs text-gray-400">All time</span>
        </div>
        <div class="card-body">
            @php
            $statusMap = [
                'open'        => ['Open',        'bg-blue-500',   route('admin.tickets.index', ['status' => 'open'])],
                'in_progress' => ['In Progress', 'bg-yellow-500', route('admin.tickets.index', ['status' => 'in_progress'])],
                'on_hold'     => ['On Hold',     'bg-gray-400',   route('admin.tickets.index', ['status' => 'on_hold'])],
                'resolved'    => ['Resolved',    'bg-green-500',  route('admin.tickets.index', ['status' => 'resolved'])],
                'closed'      => ['Closed',      'bg-gray-300',   route('admin.tickets.index', ['status' => 'closed'])],
            ];
            @endphp
            @foreach($statusMap as $status => [$label, $color, $url])
            @php $count = $byStatus[$status] ?? 0; @endphp
            <a href="{{ $url }}" class="flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-1 py-2 transition group">
                <div class="w-2.5 h-2.5 rounded-full {{ $color }} shrink-0"></div>
                <span class="flex-1 text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-700">{{ $label }}</span>
                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $count }}</span>
                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-700 mt-1">
                <div class="w-2.5 shrink-0"></div>
                <span class="flex-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Total</span>
                <span class="font-bold text-gray-900 dark:text-gray-100">{{ array_sum($byStatus) }}</span>
            </div>
        </div>
    </div>

    {{-- IT Staff Performance --}}
    <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">IT Staff Performance</h2>
            <span class="text-xs text-gray-400">Period</span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th class="text-center">Assigned</th>
                        <th class="text-center">Resolved Today</th>
                        <th class="text-center">Period Resolved</th>
                        <th class="text-center">SLA Breaches</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffPerformance as $staff)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 shrink-0">
                                    {{ $staff->initials }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-800">{{ $staff->name }}</p>
                                    <p class="text-[10px] text-gray-400">
                                        {{ $staff->roles->first()?->name === 'it_head' ? 'IT Head' : 'IT Staff' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="font-semibold text-gray-700">{{ $staff->assigned_total }}</span>
                        </td>
                        <td class="text-center">
                            <span class="{{ $staff->resolved_today > 0 ? 'text-green-600 font-bold' : 'text-gray-400' }}">
                                {{ $staff->resolved_today }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="font-semibold text-blue-700">{{ $staff->resolved_period }}</span>
                        </td>
                        <td class="text-center">
                            <span class="{{ $staff->sla_breaches > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                {{ $staff->sla_breaches }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($staff->sla_breaches > 0)
                                <span class="badge bg-red-100 text-red-700 text-[10px]">Overdue</span>
                            @elseif($staff->assigned_total > 5)
                                <span class="badge bg-yellow-100 text-yellow-700 text-[10px]">Busy</span>
                            @else
                                <span class="badge bg-green-100 text-green-700 text-[10px]">On Track</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6 text-gray-400 text-sm">No IT staff found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Category widgets row ──────────────────────────────────────────────── --}}
<div class="mb-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tickets by Category</h2>
        <span class="text-xs text-gray-400">Click any card to filter tickets</span>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @php
        $catColors = ['blue','indigo','cyan','purple','pink','red','green','orange','yellow','gray'];
        $colorMap = [
            'blue'   => ['bg-blue-50 hover:bg-blue-100 border-blue-200',   'bg-blue-500',   'text-blue-700'],
            'indigo' => ['bg-indigo-50 hover:bg-indigo-100 border-indigo-200', 'bg-indigo-500', 'text-indigo-700'],
            'cyan'   => ['bg-cyan-50 hover:bg-cyan-100 border-cyan-200',   'bg-cyan-500',   'text-cyan-700'],
            'purple' => ['bg-purple-50 hover:bg-purple-100 border-purple-200', 'bg-purple-500', 'text-purple-700'],
            'pink'   => ['bg-pink-50 hover:bg-pink-100 border-pink-200',   'bg-pink-500',   'text-pink-700'],
            'red'    => ['bg-red-50 hover:bg-red-100 border-red-200',     'bg-red-500',    'text-red-700'],
            'green'  => ['bg-green-50 hover:bg-green-100 border-green-200', 'bg-green-500',  'text-green-700'],
            'orange' => ['bg-orange-50 hover:bg-orange-100 border-orange-200', 'bg-orange-500', 'text-orange-700'],
            'yellow' => ['bg-yellow-50 hover:bg-yellow-100 border-yellow-200', 'bg-yellow-500', 'text-yellow-700'],
            'gray'   => ['bg-gray-50 hover:bg-gray-100 border-gray-200',   'bg-gray-400',   'text-gray-600'],
        ];
        $totalCatTickets = $byCategory->sum('tickets_count') ?: 1;
        @endphp

        @foreach($byCategory as $i => $cat)
        @php
            $colorKey = $catColors[$i % count($catColors)];
            [$bgCls, $dotCls, $numCls] = $colorMap[$colorKey];
            $pct = round(($cat->tickets_count / $totalCatTickets) * 100);
        @endphp
        <a href="{{ route('admin.tickets.index', ['category_id' => $cat->id]) }}"
           class="border rounded-xl p-3.5 transition-all duration-150 cursor-pointer group {{ $bgCls }}">
            <div class="flex items-start justify-between mb-2">
                <div class="w-7 h-7 rounded-lg {{ $dotCls }} flex items-center justify-center text-white text-[10px] font-bold">
                    {{ strtoupper(substr($cat->name, 0, 1)) }}
                </div>
                <span class="text-xl font-bold {{ $numCls }}">{{ $cat->tickets_count }}</span>
            </div>
            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 leading-tight mb-2 group-hover:text-gray-900">
                {{ $cat->name }}
            </p>
            <div class="w-full bg-white/60 rounded-full h-1.5">
                <div class="{{ $dotCls }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">{{ $pct }}% of total</p>
        </a>
        @endforeach
    </div>
</div>

{{-- ── Recent Activity feed + Urgent tickets ────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Recent Activity Feed --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Recent Activity</h2>
            <a href="{{ route('admin.activity.index') }}" class="text-xs text-blue-600 hover:underline">View full log →</a>
        </div>
        <div class="card-body divide-y divide-gray-50 dark:divide-gray-700 -my-1 max-h-80 overflow-y-auto">
            @forelse($recentActivity as $act)
            @php
                $iconColor = match($act->action) {
                    'created'          => 'bg-blue-100 text-blue-600',
                    'status_changed'   => 'bg-yellow-100 text-yellow-600',
                    'assigned'         => 'bg-purple-100 text-purple-600',
                    'priority_changed' => 'bg-orange-100 text-orange-600',
                    'comment_added'    => 'bg-green-100 text-green-600',
                    default            => 'bg-gray-100 text-gray-500',
                };
                $icon = match($act->action) {
                    'created'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
                    'status_changed'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                    'assigned'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
                    'comment_added'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                    default            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                };
            @endphp
            <div class="flex items-start gap-3 py-2.5">
                <div class="w-7 h-7 rounded-lg {{ $iconColor }} flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-snug">
                        <span class="font-semibold">{{ $act->user?->name ?? 'System' }}</span>
                        {{ str_replace('_', ' ', $act->action) }}
                        @if($act->ticket)
                            <a href="{{ route('admin.tickets.show', $act->ticket) }}"
                               class="font-mono text-blue-600 hover:underline">
                                {{ $act->ticket->ticket_number }}
                            </a>
                        @endif
                    </p>
                    @if($act->description)
                        <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ $act->description }}</p>
                    @endif
                </div>
                <span class="text-[10px] text-gray-400 whitespace-nowrap shrink-0">
                    {{ $act->created_at->diffForHumans(null, true, true) }}
                </span>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-6">No recent activity.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Urgent Tickets --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Urgent Tickets</h2>
            <a href="{{ route('admin.tickets.index', ['priority_id' => \App\Models\Priority::where('name','Urgent')->value('id')]) }}"
               class="text-xs text-blue-600 hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto max-h-80">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Dept</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($urgentTickets as $ticket)
                    <tr class="{{ $ticket->sla_status === 'breached' ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                        <td>
                            <a href="{{ route('admin.tickets.show', $ticket) }}"
                               class="font-mono text-xs font-semibold text-blue-600 hover:underline block">
                                {{ $ticket->ticket_number }}
                            </a>
                            <span class="text-[10px] text-gray-500 block truncate max-w-[130px]">{{ $ticket->title }}</span>
                        </td>
                        <td class="text-xs text-gray-500">{{ Str::limit($ticket->department->name, 12) }}</td>
                        <td><x-ticket-badge :status="$ticket->status"/></td>
                        <td><x-sla-indicator :ticket="$ticket"/></td>
                        <td class="text-xs text-gray-600">{{ $ticket->assignee?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-6 text-gray-400 text-sm">No urgent tickets.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const deptData = @json($byDepartment);
const catData  = @json($byCategory->map(fn($c) => ['label' => $c->name, 'count' => (int)$c->tickets_count, 'id' => $c->id]));

// ── Department HORIZONTAL bar chart ────────────────────────────────────────
// Sort descending so highest bar is at top
const sortedDept = [...deptData].sort((a, b) => b.count - a.count);

new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: {
        labels: sortedDept.map(d => d.label),
        datasets: [{
            label: 'Tickets',
            data: sortedDept.map(d => d.count),
            backgroundColor: sortedDept.map((_, i) => `hsl(${210 + i * 8}, 70%, ${55 + i * 2}%)`),
            borderRadius: 6,
            barThickness: 22,
        }]
    },
    options: {
        indexAxis: 'y',  // ← horizontal bars — labels are Y axis, no overlap
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.parsed.x} ticket${ctx.parsed.x !== 1 ? 's' : ''}`
                }
            },
            // Data labels on bars
            datalabels: false,
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { stepSize: 1, font: { size: 11 } },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            y: {
                ticks: {
                    font: { size: 11 },
                    // Shorten long department names
                    callback: function(value) {
                        const label = this.getLabelForValue(value);
                        return label.length > 20 ? label.substring(0, 18) + '…' : label;
                    }
                },
                grid: { display: false }
            }
        },
        onClick: (e, elements) => {
            if (elements.length) {
                const dept = sortedDept[elements[0].index];
                const deptIdMap = @json(\App\Models\Department::pluck('id', 'name'));
                if (deptIdMap[dept.label]) {
                    window.location.href = "{{ route('admin.tickets.index') }}?department_id=" + deptIdMap[dept.label];
                }
            }
        }
    }
});

// ── Category doughnut chart ─────────────────────────────────────────────────
const palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#6366f1','#ec4899','#14b8a6'];
const totalCat = catData.reduce((s, d) => s + d.count, 0) || 1;

new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: catData.map(d => d.label),
        datasets: [{
            data: catData.map(d => d.count),
            backgroundColor: palette,
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        cutout: '60%',
        plugins: {
            legend: {
                display: false  // ← hidden — use tooltip instead (no tiny unreadable legend)
            },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const pct = Math.round((ctx.parsed / totalCat) * 100);
                        return ` ${ctx.label}: ${ctx.parsed} ticket${ctx.parsed !== 1 ? 's' : ''} (${pct}%)`;
                    }
                }
            }
        },
        onClick: (e, elements) => {
            if (elements.length) {
                const cat = catData[elements[0].index];
                window.location.href = "{{ route('admin.tickets.index') }}?category_id=" + cat.id;
            }
        }
    },
    plugins: [{
        // Center text showing total
        id: 'centerText',
        afterDraw(chart) {
            const { ctx, chartArea: { top, bottom, left, right } } = chart;
            const centerX = (left + right) / 2;
            const centerY = (top + bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = '#374151';
            ctx.font = 'bold 22px Inter, sans-serif';
            ctx.fillText(totalCat, centerX, centerY + 4);
            ctx.font = '11px Inter, sans-serif';
            ctx.fillStyle = '#9ca3af';
            ctx.fillText('tickets', centerX, centerY + 20);
            ctx.restore();
        }
    }]
});
</script>
@endpush
