@extends('layouts.app')
@section('title', 'Add User')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Users
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Add New User</h1>
</div>

<div class="max-w-xl">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-400 @enderror" required placeholder="user@tmcwd.gov.ph">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" class="form-input @error('password') border-red-400 @enderror" required>
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" class="form-input" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" class="form-select @error('department_id') border-red-400 @enderror" required>
                            <option value="">Select...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" class="form-select @error('role') border-red-400 @enderror" required>
                            <option value="requester" {{ old('role') === 'requester' ? 'selected' : '' }}>Requester (Employee)</option>
                            <option value="it_staff"  {{ old('role') === 'it_staff'  ? 'selected' : '' }}>IT Staff (Technician)</option>
                            <option value="it_head"   {{ old('role') === 'it_head'   ? 'selected' : '' }}>IT Head (Supervisor)</option>
                        </select>
                        @error('role')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm text-gray-700">Account is active</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Create User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
