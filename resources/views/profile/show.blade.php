@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
    <p class="text-sm text-gray-500 mt-1">Manage your account information and password.</p>
</div>

<div class="max-w-2xl space-y-6">

    {{-- ── Profile Info ─────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Account Information</h2>
        </div>
        <div class="card-body">

            {{-- Success banner --}}
            @if(session('success'))
            <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf @method('PATCH')

                {{-- Avatar initials --}}
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-16 h-16 rounded-2xl bg-blue-700 flex items-center justify-center text-white text-2xl font-bold shrink-0">
                        {{ $user->initials }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @php $role = $user->roles->first()?->name ?? 'user'; @endphp
                            <span class="badge {{ match($role) {
                                'it_head'  => 'bg-purple-100 text-purple-700',
                                'it_staff' => 'bg-blue-100 text-blue-700',
                                default    => 'bg-gray-100 text-gray-600',
                            } }}">{{ match($role) {
                                'it_head'  => 'IT Head',
                                'it_staff' => 'IT Staff',
                                default    => ucfirst($role),
                            } }}</span>
                            &nbsp;·&nbsp; {{ $user->department?->name ?? 'No department' }}
                        </p>
                    </div>
                </div>

                <div>
                    <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}"
                           class="form-input @error('name') border-red-400 @enderror"
                           required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" value="{{ $user->email }}" class="form-input bg-gray-50 text-gray-400" disabled>
                    <p class="text-xs text-gray-400 mt-1">Email cannot be changed. Contact your IT Administrator if needed.</p>
                </div>

                <div>
                    <label class="form-label">Department</label>
                    <input type="text" value="{{ $user->department?->name ?? '—' }}" class="form-input bg-gray-50 text-gray-400" disabled>
                </div>

                <div class="pt-1">
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Change Password ──────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Change Password</h2>
        </div>
        <div class="card-body">

            @if(session('password_success'))
            <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('password_success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label for="current_password" class="form-label">Current Password <span class="text-red-500">*</span></label>
                    <input type="password" id="current_password" name="current_password"
                           class="form-input @error('current_password') border-red-400 @enderror"
                           autocomplete="current-password" required>
                    @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="form-label">New Password <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password"
                           class="form-input @error('password') border-red-400 @enderror"
                           autocomplete="new-password" required>
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Minimum 8 characters, must include letters and numbers.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-red-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-input @error('password_confirmation') border-red-400 @enderror"
                           autocomplete="new-password" required>
                    @error('password_confirmation')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="pt-1">
                    <button type="submit" class="btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Account Stats ─────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Account Stats</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-center">
                @role('requester')
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-2xl font-bold text-blue-700">{{ $user->tickets()->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Tickets</p>
                </div>
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="text-2xl font-bold text-green-700">{{ $user->tickets()->where('status','resolved')->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Resolved</p>
                </div>
                <div class="rounded-xl bg-yellow-50 p-4">
                    <p class="text-2xl font-bold text-yellow-700">{{ $user->tickets()->whereIn('status',['open','in_progress','on_hold'])->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Open</p>
                </div>
                @endrole

                @role('it_staff')
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-2xl font-bold text-blue-700">{{ $user->assignedTickets()->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Assigned Total</p>
                </div>
                <div class="rounded-xl bg-green-50 p-4">
                    <p class="text-2xl font-bold text-green-700">{{ $user->assignedTickets()->where('status','resolved')->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Resolved</p>
                </div>
                <div class="rounded-xl bg-yellow-50 p-4">
                    <p class="text-2xl font-bold text-yellow-700">{{ $user->assignedTickets()->whereNotIn('status',['resolved','closed'])->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Active</p>
                </div>
                @endrole

                @role('it_head')
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-2xl font-bold text-blue-700">{{ \App\Models\Ticket::count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Tickets</p>
                </div>
                <div class="rounded-xl bg-purple-50 p-4">
                    <p class="text-2xl font-bold text-purple-700">{{ \App\Models\User::count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Users</p>
                </div>
                <div class="rounded-xl bg-red-50 p-4">
                    <p class="text-2xl font-bold text-red-700">{{ \App\Models\Ticket::whereNotIn('status',['resolved','closed'])->where('sla_due_at','<',now())->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">SLA Breaches</p>
                </div>
                @endrole
            </div>
        </div>
    </div>

</div>
@endsection
