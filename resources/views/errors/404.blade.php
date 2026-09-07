<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found — TMCWD IT Service</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-blue-50 flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-5xl font-bold text-gray-800 mb-2">404</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Page Not Found</h2>
        <p class="text-gray-500 mb-8">The page you are looking for doesn't exist or has been moved.</p>
        <a href="{{ url()->previous() }}" class="btn-secondary mr-2">Go Back</a>
        <a href="{{ url('/') }}" class="btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
