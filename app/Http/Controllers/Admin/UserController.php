<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'roles'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.users.create', compact('departments'));
    }

    public function store(StoreUserRequest $request)
    {
        $data        = $request->validated();
        $role        = $data['role'];
        $data['is_active'] = $request->boolean('is_active', true);
        // All new accounts created by admin must change their password on first login
        $data['must_change_password'] = true;
        unset($data['role'], $data['password_confirmation']);

        $user = User::create($data);
        $user->assignRole($role);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function edit(User $user)
    {
        $departments = Department::active()->orderBy('name')->get();
        $userRole    = $user->roles->first()?->name ?? 'requester';

        return view('admin.users.edit', compact('user', 'departments', 'userRole'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role'], $data['password_confirmation']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);
        $user->syncRoles([$role]);

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name}'s account updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        // Soft-deactivate instead of hard delete to preserve ticket history
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', "{$name}'s account has been deactivated.");
    }
}
