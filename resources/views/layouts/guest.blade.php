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
    <style>
        /* ── Background slideshow ───────────────────────────────────────── */
        .bg-slideshow {
            position: fixed;
            inset: 0;
            z-index: 0;
        }
        .bg-slideshow .slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }
        .bg-slideshow .slide.active {
            opacity: 1;
        }
        /* Dark overlay on top of photos so text stays readable */
        .bg-slideshow::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(15, 30, 80, 0.60);
            z-index: 1;
        }
        .login-content {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased text-sm overflow-hidden">

    {{-- ── Background Photo Slideshow ─────────────────────────────────── --}}
    <div class="bg-slideshow" id="bgSlideshow">
        <div class="slide active"
             style="background-image: url('{{ asset('images/picture1.jpg') }}')"></div>
        <div class="slide"
             style="background-image: url('{{ asset('images/picture2.jpg') }}')"></div>
        <div class="slide"
             style="background-image: url('{{ asset('images/picture3.jpg') }}')"></div>
    </div>

    {{-- ── Login Content ────────────────────────────────────────────────── --}}
    <div class="login-content min-h-screen flex items-center justify-center p-4">
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
                <h1 class="text-lg font-bold text-white leading-tight drop-shadow">
                    Trece Martires City Water District
                </h1>
                <p class="text-blue-200 text-xs mt-0.5 drop-shadow">IT Request Service System</p>
            </div>

            {{-- Error flash --}}
            @if (session('error'))
                <div class="mb-3 flex items-center gap-2 px-3 py-2.5 bg-red-500/20 border border-red-400/30
                            text-white rounded-xl text-xs backdrop-blur-sm">
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
                    <h2 class="text-base font-bold text-gray-800 mb-0.5">Welcome back</h2>
                    <p class="text-gray-400 text-xs mb-5">Sign in to your account</p>
                    {{ $slot }}
                </div>
            </div>

            {{-- Slide dots indicator --}}
            <div class="flex justify-center gap-1.5 mt-4" id="slideDots">
                <span class="w-1.5 h-1.5 rounded-full bg-white/80 transition-all duration-300"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-white/30 transition-all duration-300"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-white/30 transition-all duration-300"></span>
            </div>

            <p class="text-center text-white/40 text-[10px] mt-3">
                &copy; {{ date('Y') }} Trece Martires City Water District
            </p>
        </div>
    </div>

    {{-- ── Slideshow JS ─────────────────────────────────────────────────── --}}
    <script>
        (function () {
            var slides = document.querySelectorAll('#bgSlideshow .slide');
            var dots   = document.querySelectorAll('#slideDots span');
            var current = 0;

            function goTo(index) {
                slides[current].classList.remove('active');
                dots[current].style.backgroundColor   = 'rgba(255,255,255,0.3)';
                dots[current].style.width = '6px';

                current = index;

                slides[current].classList.add('active');
                dots[current].style.backgroundColor   = 'rgba(255,255,255,0.9)';
                dots[current].style.width = '18px';
                dots[current].style.borderRadius = '4px';
            }

            // Auto-advance every 5 seconds
            setInterval(function () {
                goTo((current + 1) % slides.length);
            }, 5000);

            // Click dots to jump to slide
            dots.forEach(function (dot, i) {
                dot.style.cursor = 'pointer';
                dot.addEventListener('click', function () { goTo(i); });
            });

            // Initialize first dot
            goTo(0);
        })();
    </script>

</body>
</html>
