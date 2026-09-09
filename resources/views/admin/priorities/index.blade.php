@extends('layouts.app')
@section('title', 'Priorities & SLA Settings')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Priorities & SLA Settings</h1>
    <p class="text-sm text-gray-500 mt-1">Configure the resolution time targets for each priority level.</p>
</div>

@if(session('error'))
<div class="mb-4 flex items-center gap-2.5 px-3 py-2.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs shadow-sm">
    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach($priorities as $priority)
    @php
        $border = match(strtolower($priority->name)) {
            'urgent' => 'border-t-4 border-t-red-500',
            'high'   => 'border-t-4 border-t-orange-500',
            'medium' => 'border-t-4 border-t-yellow-500',
            default  => 'border-t-4 border-t-blue-500',
        };
        $badge = $priority->badgeClasses();
    @endphp
    <div class="card {{ $border }}">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <span class="badge {{ $badge }} text-sm px-3 py-1">{{ $priority->name }}</span>
                <span class="text-2xl font-bold text-gray-800">{{ $priority->sla_hours }}h</span>
            </div>
            <p class="text-xs text-gray-500 mb-4">
                @if($priority->sla_hours < 24)
                    Target: {{ $priority->sla_hours }} hour(s) from submission
                @else
                    Target: {{ round($priority->sla_hours / 24, 1) }} day(s) from submission
                @endif
            </p>
            <form method="POST" action="{{ route('admin.priorities.update', $priority) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="form-label text-xs">SLA Hours</label>
                    <input type="number" name="sla_hours" value="{{ $priority->sla_hours }}"
                           min="0.1" max="9999" step="0.5"
                           class="form-input text-sm @error('sla_hours') border-red-400 @enderror">
                    @error('sla_hours')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label text-xs">Color Name</label>
                    <input type="text" name="color_code" value="{{ $priority->color_code }}"
                           class="form-input text-sm" placeholder="e.g. red, orange">
                </div>
                <button type="submit" class="btn-primary btn-sm w-full justify-center">Save</button>
            </form>

            {{-- Delete priority --}}
            <div class="mt-3 pt-3 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.priorities.destroy', $priority) }}"
                      onsubmit="return confirm('Delete the \'{{ addslashes($priority->name) }}\' priority?\n\nExisting tickets will keep this priority label but it will be removed from new ticket dropdowns.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium
                                   rounded-lg border border-red-200 text-red-600 bg-white hover:bg-red-50 transition-colors">
                        Delete Priority
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-6 card">
    <div class="card-body">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">SLA Guide</h3>
        <p class="text-sm text-gray-600">SLA hours define the maximum time from ticket submission to resolution.
        Tickets approaching breach (within 20% of remaining time) are flagged with a ⚠️ warning.
        Tickets past their SLA due date are flagged 🔴 breached on the Agent and Admin dashboards.</p>
    </div>
</div>
@endsection
