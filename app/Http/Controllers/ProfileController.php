<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update name (and optionally email in future).
     */
    public function updateInfo(UpdateProfileRequest $request)
    {
        $request->user()->update([
            'name' => $request->validated('name'),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Change password — requires current password verification.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $request->user()->update([
            'password' => bcrypt($request->validated('password')),
        ]);

        return back()->with('password_success', 'Password changed successfully.');
    }
}
