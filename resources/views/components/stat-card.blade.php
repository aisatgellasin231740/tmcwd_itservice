@props([
    'label',
    'value',
    'color'     => 'blue',
    'icon'      => null,
    'suffix'    => '',
    'filterKey' => null,   // e.g. 'all' | 'open' | 'resolved' | 'closed'
    'active'    => false,  // true when this card's filter is currently applied
    'trend'     => null,   // int delta vs 7 days ago, or null to hide
    'href'      => null,   // URL to navigate to on click
])

@php
    $isZero = (int) $value === 0;

    // ── Border / accent colours ────────────────────────────────────────────
    $borderColor = match($color) {
        'green'  => 'border-l-green-500',
        'yellow' => 'border-l-yellow-500',
        'red'    => 'border-l-red-500',
        'orange' => 'border-l-orange-500',
        'gray'   => 'border-l-gray-400',
        default  => 'border-l-blue-500',
    };

    // Active state: filled left border + subtle tinted background ring
    $activeBg = match($color) {
        'green'  => 'ring-1 ring-green-200 bg-green-50',
        'yellow' => 'ring-1 ring-yellow-200 bg-yellow-50',
        'gray'   => 'ring-1 ring-gray-200 bg-gray-50',
        default  => 'ring-1 ring-blue-200 bg-blue-50',
    };

    // ── Number colour — muted when zero ───────────────────────────────────
    $numColor = $isZero
        ? 'text-gray-300'
        : match($color) {
            'green'  => 'text-green-600',
            'yellow' => 'text-yellow-600',
            'red'    => 'text-red-600',
            'orange' => 'text-orange-600',
            default  => 'text-blue-700',
        };

    // ── Card wrapper classes ──────────────────────────────────────────────
    $cardClasses = 'card border-l-4 ' . $borderColor;
    if ($active) {
        $cardClasses .= ' ' . $activeBg;
    }
    if ($isZero) {
        $cardClasses .= ' opacity-60';
    }
    if ($href) {
        $cardClasses .= ' cursor-pointer hover:shadow-md transition-shadow duration-150';
    }

    // ── Trend display ─────────────────────────────────────────────────────
    $showTrend   = $trend !== null;
    $trendUp     = $trend > 0;
    $trendDown   = $trend < 0;
    $trendFlat   = $trend === 0;
    $trendAbs    = $trend !== null ? abs($trend) : 0;

    $trendText = match(true) {
        $trendFlat  => 'no change from last week',
        $trendUp    => '+' . $trendAbs . ' from last week',
        $trendDown  => '-' . $trendAbs . ' from last week',
        default     => '',
    };
    $trendColor = match(true) {
        $trendUp    => 'text-emerald-600',
        $trendDown  => 'text-red-500',
        default     => 'text-gray-400',
    };
@endphp

@if($href)
<a href="{{ $href }}" class="{{ $cardClasses }}" aria-current="{{ $active ? 'true' : 'false' }}">
@else
<div class="{{ $cardClasses }}">
@endif

    <div class="card-body flex items-center justify-between py-5">
        <div class="min-w-0">
            {{-- Label --}}
            <p class="text-xs font-medium {{ $isZero ? 'text-gray-400' : 'text-gray-500' }} uppercase tracking-wide mb-1">
                {{ $label }}
            </p>

            {{-- Main number --}}
            <p class="text-3xl font-bold {{ $numColor }} leading-none">
                {{ $value }}<span class="text-base font-normal text-gray-400 ml-1">{{ $suffix }}</span>
            </p>

            {{-- Trend line --}}
            @if($showTrend)
            <p class="mt-1.5 flex items-center gap-1 text-xs {{ $trendColor }}">
                @if($trendUp)
                    {{-- Arrow up --}}
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                    </svg>
                @elseif($trendDown)
                    {{-- Arrow down --}}
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                @else
                    {{-- Minus / flat --}}
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14"/>
                    </svg>
                @endif
                <span>{{ $trendText }}</span>
            </p>
            @endif
        </div>

        @if($icon)
        <div class="w-12 h-12 rounded-xl {{ $isZero ? 'bg-gray-100' : 'bg-' . $color . '-50' }} flex items-center justify-center shrink-0 ml-3">
            {!! $icon !!}
        </div>
        @endif
    </div>

@if($href)
</a>
@else
</div>
@endif
