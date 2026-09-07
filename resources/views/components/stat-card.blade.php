@props(['label', 'value', 'color' => 'blue', 'icon' => null, 'suffix' => ''])

@php
    $ringColor = match($color) {
        'green'  => 'border-l-green-500',
        'yellow' => 'border-l-yellow-500',
        'red'    => 'border-l-red-500',
        'orange' => 'border-l-orange-500',
        'gray'   => 'border-l-gray-400',
        default  => 'border-l-blue-500',
    };
    $numColor = match($color) {
        'green'  => 'text-green-600',
        'yellow' => 'text-yellow-600',
        'red'    => 'text-red-600',
        'orange' => 'text-orange-600',
        default  => 'text-blue-700',
    };
@endphp

<div class="card border-l-4 {{ $ringColor }}">
    <div class="card-body flex items-center justify-between py-5">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
            <p class="text-3xl font-bold {{ $numColor }}">{{ $value }}<span class="text-base font-normal text-gray-400 ml-1">{{ $suffix }}</span></p>
        </div>
        @if ($icon)
            <div class="w-12 h-12 rounded-xl bg-{{ $color }}-50 flex items-center justify-center shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
