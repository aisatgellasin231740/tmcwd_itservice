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
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-lg mb-4">
                <img src="{{ asset('images/tmcwd-logo.png') }}"
                     alt="TMCWD Logo"
                     class="w-14 h-14 object-contain"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <div style="display:none" class="w-14 h-14 bg-blue-700 rounded-xl items-center justify-center">
                    <span class="text-white font-bold text-lg">IT</span>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-white">Trece Martires City Water District</h1>
            <p class="text-blue-200 text-sm mt-1">IT Request Service System</p>
        </div>

        {{-- Flash Error (deactivated account, etc.) --}}
        @if (session('error'))
            <div class="mb-4 px-4 py-3 bg-red-100 border border-red-300 text-red-700 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl px-8 py-8">
            {{ $slot }}
        </div>

        <p class="text-center text-blue-300 text-xs mt-6">
            &copy; {{ date('Y') }} Trece Martires City Water District. All rights reserved.
        </p>
    </div>

</body>
</html>
