<x-guest-layout>
    <x-auth-session-status class="mb-3 text-xs" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
            <div class="relative">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="w-full pl-9 pr-3 py-2.5 text-sm border-2 rounded-xl transition
                              {{ $errors->get('email') ? 'border-red-400' : 'border-gray-200 focus:border-blue-500' }}
                              focus:outline-none"
                       placeholder="you@tmcwd.gov.ph"
                       required autofocus autocomplete="username">
            </div>
            @error('email')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-semibold text-gray-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] text-blue-600 hover:underline">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative" x-data="{ show: false }">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password" :type="show ? 'text' : 'password'" name="password"
                       class="w-full pl-9 pr-9 py-2.5 text-sm border-2 rounded-xl transition
                              {{ $errors->get('password') ? 'border-red-400' : 'border-gray-200 focus:border-blue-500' }}
                              focus:outline-none"
                       placeholder="••••••••"
                       required autocomplete="current-password">
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
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" id="remember_me" name="remember"
                   class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
            <label for="remember_me" class="text-xs text-gray-600 cursor-pointer">Keep me signed in</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold
                       rounded-xl shadow-md shadow-blue-500/20 focus:outline-none focus:ring-2
                       focus:ring-blue-500 focus:ring-offset-2 transition flex items-center justify-center gap-2 group">
            Sign In
            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>
</x-guest-layout>
