<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased text-sm"
      style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #0284c7 100%);">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-sm">

            {{-- Logo --}}
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-3 ring-2 ring-white/20">
                    <img src="{{ asset('images/tmcwd-logo.png') }}" alt="TMCWD"
                         class="w-14 h-14 object-contain rounded-xl"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none" class="w-14 h-14 bg-blue-600 rounded-xl items-center justify-center">
                        <span class="text-white font-bold text-lg">IT</span>
                    </div>
                </div>
                <h1 class="text-lg font-bold text-white leading-tight">
                    Trece Martires City Water District
                </h1>
                <p class="text-blue-200 text-xs mt-0.5">IT Request Service System</p>
            </div>

            {{-- Error flash --}}
            @if (session('error'))
                <div class="mb-3 flex items-center gap-2 px-3 py-2.5 bg-red-500/20 border border-red-400/30
                            text-white rounded-xl text-xs">
                    <svg class="w-3.5 h-3.5 shrink-0 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-blue-500 to-cyan-400"></div>
                <div class="px-6 py-6">
                    <h2 class="text-base font-bold text-gray-800 mb-0.5">Welcome back 👋</h2>
                    <p class="text-gray-400 text-xs mb-5">Sign in to your account</p>
                    {{ $slot }}
                </div>
            </div>

            <p class="text-center text-blue-300/60 text-[10px] mt-4">
                &copy; {{ date('Y') }} Trece Martires City Water District
            </p>
        </div>
    </div>
</body>
</html>
