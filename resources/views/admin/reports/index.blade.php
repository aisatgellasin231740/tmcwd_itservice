@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">IT Performance Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Ticket analysis for the selected date range.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.csv', request()->query()) }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export CSV
        </a>
        <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export PDF
        </a>
    </div>
</div>

{{-- Date Range Filter --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end" data-auto-filter>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input">
            </div>
            <button type="submit" class="btn-primary">Apply</button>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <x-stat-card label="Total Tickets"    :value="$summary['total']"          color="blue"/>
    <x-stat-card label="Open"             :value="$summary['open']"           color="yellow"/>
    <x-stat-card label="Resolved"         :value="$summary['resolved']"       color="green"/>
    <x-stat-card label="Closed"           :value="$summary['closed']"         color="gray"/>
    <x-stat-card label="SLA Breaches"     :value="$summary['sla_breach']"     color="red"/>
    <x-stat-card label="Avg Resolution"   :value="$summary['avg_resolution']" suffix="hrs" color="orange"/>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Volume by Department --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Ticket Volume by Department</h2>
        </div>
        <div class="card-body">
            <canvas id="deptReportChart" height="220" class="mb-4"></canvas>
            <table class="data-table mt-2">
                <thead><tr><th>Department</th><th class="text-right">Tickets</th></tr></thead>
                <tbody>
                    @foreach($byDepartment as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td class="text-right font-semibold">{{ $row->ticket_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Avg Resolution by Category --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Avg Resolution Time by Category</h2>
        </div>
        <div class="card-body">
            <table class="data-table">
                <thead><tr><th>Category</th><th class="text-right">Resolved</th><th class="text-right">Avg Hours</th></tr></thead>
                <tbody>
                    @foreach($byCategory as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td class="text-right text-gray-500">{{ $row->resolved_count }}</td>
                        <td class="text-right font-semibold">
                            @if($row->avg_hours)
                                {{ $row->avg_hours }}h
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
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
new Chart(document.getElementById('deptReportChart'), {
    type: 'bar',
    data: {
        labels: deptData.map(d => d.name),
        datasets: [{ label: 'Tickets', data: deptData.map(d => d.ticket_count),
            backgroundColor: '#3b82f6', borderRadius: 5 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } },
                  x: { ticks: { maxRotation: 35, font: { size: 10 } } } }
    }
});
</script>
@endpush
