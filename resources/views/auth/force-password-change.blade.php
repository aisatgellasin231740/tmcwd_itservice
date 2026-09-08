<x-guest-layout>
    {{-- Override the card header text --}}
    @push('styles')
    <style>
        /* The guest layout already shows "Welcome back 👋" — override it via a slot isn't
           available in this layout, so we use a simple full-card replacement approach. */
    </style>
    @endpush

    {{-- Header notice --}}
    <div class="mb-5 flex items-start gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">Password change required</p>
            <p class="text-xs text-amber-700 mt-0.5">
                For your security, you must set a new password before continuing.
                Your new password cannot be the same as your current one.
            </p>
        </div>
    </div>

    {{-- Who is logged in --}}
    <div class="flex items-center gap-2 mb-4 px-1">
        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 shrink-0">
            {{ auth()->user()->initials }}
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-700">{{ auth()->user()->name }}</p>
            <p class="text-[10px] text-gray-400">{{ auth()->user()->email }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.force-change.update') }}" class="space-y-4">
        @csrf

        {{-- New Password --}}
        <div>
            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                New Password <span class="text-red-500">*</span>
            </label>
            <div class="relative" x-data="{ show: false }">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password"
                       :type="show ? 'text' : 'password'"
                       name="password"
                       class="w-full pl-9 pr-9 py-2.5 text-sm border-2 rounded-xl transition
                              {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-500' }}
                              focus:outline-none"
                       placeholder="Enter new password"
                       required autocomplete="new-password">
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg x-show="!show" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-[11px] text-gray-400">
                Minimum 8 characters — must include letters and numbers.
            </p>
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">
                Confirm New Password <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password_confirmation"
                       type="password"
                       name="password_confirmation"
                       class="w-full pl-9 pr-3 py-2.5 text-sm border-2 rounded-xl transition
                              border-gray-200 focus:border-blue-500 focus:outline-none"
                       placeholder="Re-enter new password"
                       required autocomplete="new-password">
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold
                       rounded-xl shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500
                       focus:ring-offset-2 transition flex items-center justify-center gap-2 group">
            Set New Password & Continue
            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        {{-- Logout link --}}
        <div class="text-center pt-1">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 hover:underline transition">
                    Not you? Sign out
                </button>
            </form>
        </div>
    </form>
</x-guest-layout>
