@props(['ticket'])

@if (!auth()->user()->hasAnyRole(['it_staff', 'it_head']))
    {{-- Not visible to requesters --}}
@else
    @php
        $slaService = app(\App\Services\SlaService::class);
        $status     = $slaService->getStatus($ticket);
        $label      = $slaService->getTimeLabel($ticket);

        $classes = match($status) {
            'breached' => 'sla-breached',
            'warning'  => 'sla-warning',
            'paused'   => 'sla-paused',
            'met'      => 'sla-met',
            default    => 'sla-ok',
        };
        $icon = match($status) {
            'breached' => '🔴',
            'warning'  => '⚠️',
            'paused'   => '⏸',
            'met'      => '✅',
            default    => '🟢',
        };
    @endphp
    <span class="badge {{ $classes }} text-xs" title="SLA: {{ $ticket->sla_due_at?->format('M d, Y H:i') ?? 'N/A' }}">
        {{ $icon }} {{ $label }}
    </span>
@endif
