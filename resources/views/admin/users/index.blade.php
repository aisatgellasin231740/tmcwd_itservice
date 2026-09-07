@extends('layouts.app')
@section('title', 'User Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Users</h1>
    <a href="{{ route('admin.users.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add User
    </a>
</div>

<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-semibold text-blue-700 shrink-0">
                            {{ $user->initials }}
                        </div>
                        <span class="font-medium text-gray-800">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="text-gray-500 text-sm">{{ $user->email }}</td>
                <td class="text-gray-500 text-sm">{{ $user->department?->name ?? '—' }}</td>
                <td>
                    @php $role = $user->roles->first()?->name ?? 'none'; @endphp
                    <span class="badge {{ match($role) {
                        'it_head'  => 'bg-purple-100 text-purple-700',
                        'it_staff' => 'bg-blue-100 text-blue-700',
                        default    => 'bg-gray-100 text-gray-600',
                    } }}">{{ match($role) {
                        'it_head'  => 'IT Head',
                        'it_staff' => 'IT Staff',
                        default    => ucfirst($role),
                    } }}</span>
                </td>
                <td>
                    @if($user->is_active)
                        <span class="badge bg-green-100 text-green-700">Active</span>
                    @else
                        <span class="badge bg-red-100 text-red-700">Inactive</span>
                    @endif
                </td>
                <td class="whitespace-nowrap">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary btn-sm mr-1">Edit</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                          onsubmit="return confirm('Deactivate {{ addslashes($user->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm">Deactivate</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-10 text-gray-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
@endif
@endsection
