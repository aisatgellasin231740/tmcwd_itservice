<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'password.min'      => 'Password must be at least 8 characters.',
            'password.letters'  => 'Password must contain at least one letter.',
            'password.numbers'  => 'Password must contain at least one number.',
            'password.confirmed'=> 'Password confirmation does not match.',
        ]);

        // Cannot reuse the seeded "password" default
        if (\Illuminate\Support\Facades\Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => 'Your new password cannot be the same as your current password.',
            ]);
        }

        $request->user()->update([
            'password'             => bcrypt($request->password),
            'must_change_password' => false,  // ← flag cleared
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Password changed successfully. Welcome to TMCWD IT Request Service!');
    }
}
