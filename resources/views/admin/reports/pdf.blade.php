<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
    .header { background: #1e40af; color: white; padding: 16px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: bold; }
    .header p  { font-size: 10px; opacity: 0.85; margin-top: 2px; }
    .section { margin: 0 20px 14px; }
    .section-title { font-size: 11px; font-weight: bold; color: #1e40af; border-bottom: 1px solid #bfdbfe; padding-bottom: 4px; margin-bottom: 8px; }
    .stats-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; margin: 0 20px 14px; }
    .stat-box { background: #eff6ff; border-radius: 6px; padding: 8px; text-align: center; }
    .stat-box .num { font-size: 18px; font-weight: bold; color: #1d4ed8; }
    .stat-box .lbl { font-size: 8px; color: #64748b; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    th { background: #1e40af; color: white; padding: 5px 6px; text-align: left; font-weight: 600; }
    td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
    tr:nth-child(even) td { background: #f8fafc; }
    .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: 600; }
    .badge-open        { background: #dbeafe; color: #1e40af; }
    .badge-in_progress { background: #fef9c3; color: #854d0e; }
    .badge-on_hold     { background: #f1f5f9; color: #475569; }
    .badge-resolved    { background: #dcfce7; color: #166534; }
    .badge-closed      { background: #e2e8f0; color: #475569; }
    .footer { text-align: center; color: #94a3b8; font-size: 8px; margin-top: 16px; padding-top: 8px; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>

<div class="header">
    <h1>TMCWD IT Performance Report</h1>
    <p>Period: {{ \Carbon\Carbon::parse($dateFrom)->format('F j, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('F j, Y') }}</p>
    <p>Generated: {{ now()->format('F j, Y g:i A') }}</p>
</div>

{{-- Summary Stats --}}
<div class="stats-grid">
    <div class="stat-box"><div class="num">{{ $summary['total'] }}</div><div class="lbl">Total Tickets</div></div>
    <div class="stat-box"><div class="num">{{ $summary['open'] }}</div><div class="lbl">Open</div></div>
    <div class="stat-box"><div class="num">{{ $summary['resolved'] }}</div><div class="lbl">Resolved</div></div>
    <div class="stat-box"><div class="num">{{ $summary['closed'] }}</div><div class="lbl">Closed</div></div>
    <div class="stat-box"><div class="num">{{ $summary['sla_breach'] }}</div><div class="lbl">SLA Breaches</div></div>
    <div class="stat-box"><div class="num">{{ $summary['avg_resolution'] }}h</div><div class="lbl">Avg Resolution</div></div>
</div>

{{-- Ticket Listing --}}
<div class="section">
    <div class="section-title">Ticket Listing ({{ $tickets->count() }} records)</div>
    <table>
        <thead>
            <tr>
                <th style="width:12%">Ticket #</th>
                <th style="width:22%">Title</th>
                <th style="width:12%">Requester</th>
                <th style="width:12%">Department</th>
                <th style="width:12%">Category</th>
                <th style="width:7%">Priority</th>
                <th style="width:8%">Status</th>
                <th style="width:8%">Assigned To</th>
                <th style="width:7%">Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td style="font-family: monospace; font-size: 8px;">{{ $ticket->ticket_number }}</td>
                <td>{{ \Illuminate\Support\Str::limit($ticket->title, 40) }}</td>
                <td>{{ $ticket->requester?->name }}</td>
                <td>{{ $ticket->department?->name }}</td>
                <td>{{ $ticket->category?->name }}</td>
                <td>{{ $ticket->priority?->name }}</td>
                <td><span class="badge badge-{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span></td>
                <td>{{ $ticket->assignee?->name ?? '—' }}</td>
                <td>{{ $ticket->created_at->format('m/d/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    Trece Martires City Water District — IT Request Service System &nbsp;|&nbsp;
    Confidential — For Internal Use Only
</div>
</body>
</html>
