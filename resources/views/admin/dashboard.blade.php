@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F j, Y') }}</p>
    </div>

    {{-- Period quick-select — each button is a link so URL is bookmarkable --}}
    <div class="flex flex-wrap items-center gap-2" x-data="{ showCustom: {{ $period === 'custom' ? 'true' : 'false' }} }">
        @foreach([
            'this_week' => 'This Week',
            'month'     => 'This Month',
            'quarter'   => 'This Quarter',
            'year'      => 'This Year',
            'custom'    => 'Custom',
        ] as $key => $label)
        @if($key === 'custom')
            <button @click="showCustom = !showCustom"
                    class="btn-sm {{ $period === 'custom' ? 'btn-primary' : 'btn-secondary' }}">
                {{ $label }}
            </button>
        @else
            <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
               class="btn-sm {{ $period === $key ? 'btn-primary' : 'btn-secondary' }}">
                {{ $label }}
            </a>
        @endif
        @endforeach

        {{-- Custom date range form --}}
        <div x-show="showCustom" x-cloak class="flex items-center gap-2 mt-1 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2 items-end">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="form-label text-xs">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input text-sm py-1.5">
                </div>
                <div>
                    <label class="form-label text-xs">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input text-sm py-1.5">
                </div>
                <button type="submit" class="btn-primary btn-sm">Apply</button>
            </form>
        </div>
    </div>
</div>

{{-- Active range indicator --}}
<div class="mb-5 text-xs text-gray-500 flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    Showing data from
    <span class="font-medium text-gray-700">
        {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}
    </span>
    <span class="text-gray-400">(charts &amp; resolved/avg stats)</span>
    &nbsp;·&nbsp;
    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">Reset</a>
</div>

{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <x-stat-card label="Total Open"         :value="$stats['total_open']"     color="blue"/>
    <x-stat-card label="Resolved (Period)"  :value="$stats['resolved_range']" color="green"/>
    <x-stat-card label="Closed (Period)"    :value="$stats['closed_range']"   color="gray"/>
    <x-stat-card label="SLA Breaches"       :value="$stats['sla_breached']"   color="red"/>
    <x-stat-card label="Avg Resolution"     :value="$stats['avg_resolution']" suffix="hrs" color="yellow"/>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    {{-- Bar Chart: By Department --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Tickets by Department</h2>
            <span class="text-xs text-gray-400">Period filtered</span>
        </div>
        <div class="card-body">
            <canvas id="deptChart" height="220"></canvas>
        </div>
    </div>

    {{-- Doughnut: By Category --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Tickets by Category</h2>
            <span class="text-xs text-gray-400">Period filtered</span>
        </div>
        <div class="card-body flex items-center justify-center">
            <canvas id="catChart" height="220"></canvas>
        </div>
    </div>
</div>

{{-- ═══ CATEGORY WIDGETS ═══════════════════════════════════════════════ --}}
<div class="mt-6 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-semibold text-gray-800">Tickets by Category</h2>
        <span class="text-xs text-gray-400">Period filtered — click any card to view tickets</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @php
        $catIcons = [
            'Hardware Issue'                  => ['🖥️',  'blue'],
            'Software Issue'                  => ['💿',  'indigo'],
            'Network/Internet Issue'          => ['🌐',  'cyan'],
            'Printer/Scanner Issue'           => ['🖨️',  'purple'],
            'Email/Account Access'            => ['📧',  'pink'],
            'Billing System Issue'            => ['💳',  'red'],
            'GIS/Mapping System Issue'        => ['🗺️',  'green'],
            'New Equipment Request'           => ['📦',  'orange'],
            'Account Creation/Access Request' => ['🔑',  'yellow'],
            'Other'                           => ['📋',  'gray'],
        ];
        $totalCatTickets = $byCategory->sum('tickets_count');
        @endphp

        @foreach($byCategory as $cat)
        @php
            [$icon, $color] = $catIcons[$cat->name] ?? ['📋', 'gray'];
            $pct = $totalCatTickets > 0 ? round(($cat->tickets_count / $totalCatTickets) * 100) : 0;
            $barColor = match($color) {
                'blue'   => 'bg-blue-500',
                'indigo' => 'bg-indigo-500',
                'cyan'   => 'bg-cyan-500',
                'purple' => 'bg-purple-500',
                'pink'   => 'bg-pink-500',
                'red'    => 'bg-red-500',
                'green'  => 'bg-green-500',
                'orange' => 'bg-orange-500',
                'yellow' => 'bg-yellow-500',
                default  => 'bg-gray-400',
            };
            $bgColor = match($color) {
                'blue'   => 'bg-blue-50 hover:bg-blue-100 border-blue-200',
                'indigo' => 'bg-indigo-50 hover:bg-indigo-100 border-indigo-200',
                'cyan'   => 'bg-cyan-50 hover:bg-cyan-100 border-cyan-200',
                'purple' => 'bg-purple-50 hover:bg-purple-100 border-purple-200',
                'pink'   => 'bg-pink-50 hover:bg-pink-100 border-pink-200',
                'red'    => 'bg-red-50 hover:bg-red-100 border-red-200',
                'green'  => 'bg-green-50 hover:bg-green-100 border-green-200',
                'orange' => 'bg-orange-50 hover:bg-orange-100 border-orange-200',
                'yellow' => 'bg-yellow-50 hover:bg-yellow-100 border-yellow-200',
                default  => 'bg-gray-50 hover:bg-gray-100 border-gray-200',
            };
            $numColor = match($color) {
                'blue'   => 'text-blue-700',
                'indigo' => 'text-indigo-700',
                'cyan'   => 'text-cyan-700',
                'purple' => 'text-purple-700',
                'pink'   => 'text-pink-700',
                'red'    => 'text-red-700',
                'green'  => 'text-green-700',
                'orange' => 'text-orange-700',
                'yellow' => 'text-yellow-700',
                default  => 'text-gray-600',
            };
        @endphp
        <a href="{{ route('admin.tickets.index', ['category_id' => $cat->id]) }}"
           class="border rounded-xl p-4 transition-all duration-150 cursor-pointer group {{ $bgColor }}">

            {{-- Icon + Count --}}
            <div class="flex items-start justify-between mb-3">
                <span class="text-2xl leading-none">{{ $icon }}</span>
                <span class="text-2xl font-bold {{ $numColor }} leading-none">{{ $cat->tickets_count }}</span>
            </div>

            {{-- Category name --}}
            <p class="text-xs font-semibold text-gray-700 leading-tight mb-2 group-hover:text-gray-900">
                {{ $cat->name }}
            </p>

            {{-- Progress bar --}}
            <div class="w-full bg-white/70 rounded-full h-1.5 overflow-hidden">
                <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-300"
                     style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">{{ $pct }}% of total</p>
        </a>
        @endforeach
    </div>
</div>

{{-- Status summary + Recent Urgent --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Status Summary (all-time live state) --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">By Status</h2>
            <span class="text-xs text-gray-400">All time</span>
        </div>
        <div class="card-body space-y-2">
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
            <a href="{{ $url }}" class="flex items-center gap-3 hover:bg-gray-50 rounded-lg px-1 py-1 transition group">
                <div class="w-2.5 h-2.5 rounded-full {{ $color }} shrink-0"></div>
                <span class="flex-1 text-sm text-gray-700 group-hover:text-blue-700">{{ $label }}</span>
                <span class="font-semibold text-gray-900">{{ $count }}</span>
                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100 mt-1">
                <div class="w-2.5 h-2.5 shrink-0"></div>
                <span class="flex-1 text-sm font-medium text-gray-700">Total</span>
                <span class="font-bold text-gray-900">{{ array_sum($byStatus) }}</span>
            </div>
        </div>
    </div>

    {{-- Recent Urgent Tickets --}}
    <div class="lg:col-span-2 card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Recent Urgent Tickets</h2>
            <a href="{{ route('admin.tickets.index', ['priority_id' => \App\Models\Priority::where('name','Urgent')->value('id')]) }}"
               class="text-sm text-blue-600 hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr>
                    <th>Ticket #</th><th>Title</th><th>Dept</th><th>Status</th><th>SLA</th><th>Assigned</th>
                </tr></thead>
                <tbody>
                    @forelse($urgentTickets as $ticket)
                    <tr>
                        <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="text-blue-600 hover:underline font-mono text-xs font-medium">{{ $ticket->ticket_number }}</a></td>
                        <td class="max-w-[180px] truncate text-sm">{{ $ticket->title }}</td>
                        <td class="text-xs text-gray-500">{{ Str::limit($ticket->department->name,14) }}</td>
                        <td><x-ticket-badge :status="$ticket->status"/></td>
                        <td><x-sla-indicator :ticket="$ticket"/></td>
                        <td class="text-xs text-gray-600">{{ $ticket->assignee?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6 text-gray-400 text-sm">No urgent tickets. 🎉</td></tr>
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
const catData  = @json($byCategory->map(fn($c) => ['label' => $c->name, 'count' => (int)$c->tickets_count]));

// Department Bar Chart
new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: {
        labels: deptData.map(d => d.label),
        datasets: [{
            label: 'Tickets',
            data: deptData.map(d => d.count),
            backgroundColor: '#3b82f6',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { ticks: { maxRotation: 35, font: { size: 10 } } }
        },
        onClick: (e, elements) => {
            if (elements.length) {
                const dept = deptData[elements[0].index].label;
                // Clicking a bar opens the admin ticket queue filtered by that department
                const deptId = @json(\App\Models\Department::pluck('id','name'));
                if (deptId[dept]) {
                    window.location.href = "{{ route('admin.tickets.index') }}?department_id=" + deptId[dept];
                }
            }
        }
    }
});

// Category Doughnut Chart
const palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#6366f1','#ec4899','#14b8a6'];
new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: catData.map(d => d.label),
        datasets: [{
            data: catData.map(d => d.count),
            backgroundColor: palette,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'right', labels: { font: { size: 10 }, boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed} tickets`
                }
            }
        },
        onClick: (e, elements) => {
            if (elements.length) {
                const cat = catData[elements[0].index].label;
                const catId = @json($byCategory->pluck('id','name'));
                if (catId[cat]) {
                    window.location.href = "{{ route('admin.tickets.index') }}?category_id=" + catId[cat];
                }
            }
        }
    }
});
</script>
@endpush
