<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
    .header { background: #1e40af; color: white; padding: 12px 16px; margin-bottom: 12px; }
    .header h1 { font-size: 14px; font-weight: bold; }
    .header p  { font-size: 9px; opacity: 0.8; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; font-size: 8px; margin: 0 16px; width: calc(100% - 32px); }
    th { background: #1e40af; color: white; padding: 4px 5px; text-align: left; font-weight: 600; }
    td { padding: 3px 5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    tr:nth-child(even) td { background: #f8fafc; }
    .badge { display:inline-block; padding: 1px 4px; border-radius: 3px; font-size: 7px; font-weight: 600; }
    .b-created   { background:#dbeafe; color:#1e40af; }
    .b-status    { background:#fef9c3; color:#854d0e; }
    .b-assigned  { background:#ede9fe; color:#5b21b6; }
    .b-priority  { background:#ffedd5; color:#9a3412; }
    .b-comment   { background:#dcfce7; color:#166534; }
    .b-default   { background:#f1f5f9; color:#475569; }
    .footer { text-align:center; color:#94a3b8; font-size:7px; margin: 12px 16px 0; padding-top: 6px; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="header">
    <h1>TMCWD IT Service — Activity Log</h1>
    <p>Period: {{ \Carbon\Carbon::parse($dateFrom)->format('F j, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('F j, Y') }}</p>
    <p>Generated: {{ now()->format('F j, Y g:i A') }} &nbsp;|&nbsp; Total entries: {{ $activities->count() }}</p>
</div>

<table>
    <thead>
        <tr>
            <th style="width:11%">Date & Time</th>
            <th style="width:10%">Ticket #</th>
            <th style="width:12%">Action</th>
            <th style="width:30%">Description</th>
            <th style="width:10%">Old Value</th>
            <th style="width:10%">New Value</th>
            <th style="width:17%">Performed By</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activities as $act)
        @php
            $badgeClass = match($act->action) {
                'created'          => 'b-created',
                'status_changed'   => 'b-status',
                'assigned'         => 'b-assigned',
                'priority_changed' => 'b-priority',
                'comment_added', 'attachment_added' => 'b-comment',
                default => 'b-default',
            };
        @endphp
        <tr>
            <td>{{ $act->created_at->format('m/d/Y H:i') }}</td>
            <td style="font-family:monospace; font-size:7.5px;">{{ $act->ticket?->ticket_number ?? '—' }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ str_replace('_',' ',ucfirst($act->action)) }}</span></td>
            <td>{{ \Illuminate\Support\Str::limit($act->description ?? $act->description_label, 60) }}</td>
            <td>{{ $act->old_value ?? '—' }}</td>
            <td>{{ $act->new_value ?? '—' }}</td>
            <td>{{ $act->user?->name ?? 'System' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Trece Martires City Water District — IT Request Service System &nbsp;|&nbsp; Confidential — For Internal Use Only
</div>
</body>
</html>
