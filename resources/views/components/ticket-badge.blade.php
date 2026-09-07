@props(['status' => null, 'priority' => null])

@if ($status)
    @php
        $classes = match($status) {
            'open'        => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'on_hold'     => 'bg-gray-100 text-gray-700',
            'resolved'    => 'bg-green-100 text-green-800',
            'closed'      => 'bg-gray-300 text-gray-600',
            default       => 'bg-gray-100 text-gray-700',
        };
        $label = match($status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'on_hold'     => 'On Hold',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => ucfirst($status),
        };
    @endphp
    <span class="badge {{ $classes }}">{{ $label }}</span>
@elseif ($priority)
    @php
        $classes = match(strtolower($priority)) {
            'urgent' => 'bg-red-100 text-red-700',
            'high'   => 'bg-orange-100 text-orange-700',
            'medium' => 'bg-yellow-50 text-yellow-600',
            default  => 'bg-blue-50 text-blue-600',
        };
    @endphp
    <span class="badge {{ $classes }}">{{ ucfirst($priority) }}</span>
@endif
