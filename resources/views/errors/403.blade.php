<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied — TMCWD IT Service</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-blue-50 flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-5xl font-bold text-gray-800 mb-2">403</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Access Denied</h2>
        <p class="text-gray-500 mb-8">You do not have permission to access this page. If you believe this is an error, please contact the IT Administrator.</p>
        <a href="{{ url()->previous() }}" class="btn-secondary mr-2">Go Back</a>
        <a href="{{ url('/') }}" class="btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
