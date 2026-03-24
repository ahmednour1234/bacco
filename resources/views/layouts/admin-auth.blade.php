<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Qimta Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'IBM Plex Sans Arabic', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 lg:bg-white lg:h-screen lg:overflow-hidden lg:flex antialiased">

    {{-- ══════════════════════════════════════════════════════════
         LEFT  –  Brand panel  (desktop only)
    ══════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:flex flex-col w-[46%] xl:w-1/2 h-full bg-white
                px-10 xl:px-16 py-12 relative shrink-0">

        {{-- Logo --}}
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('SVG.png') }}" alt="Qimta" class="h-9 w-9 object-contain">
            <span class="text-slate-900 text-xl font-bold tracking-tight">Qimta</span>
        </div>

        {{-- Hero copy – centred vertically --}}
        <div class="flex-1 flex flex-col justify-center">
            <div class="max-w-xs xl:max-w-sm">
                <h1 class="text-[2rem] xl:text-[2.4rem] font-extrabold text-slate-900 leading-snug mb-4">
                    @yield('left-heading', 'Building the future of construction management.')
                </h1>
                <p class="text-slate-500 text-sm leading-relaxed mb-8">
                    @yield('left-subtext', 'Streamline your projects, manage teams, and track every detail in one powerful platform designed for the modern builder.')
                </p>

                @yield('left-extra')
            </div>
        </div>

        {{-- Step dots (hidden if page sends empty section) --}}
        @yield('left-dots')

        {{-- Vertical divider --}}
        <div class="absolute right-0 top-0 h-full w-px bg-slate-100"></div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         RIGHT  –  Form panel
    ══════════════════════════════════════════════════════════ --}}
    <div class="w-full lg:flex-1 lg:h-full bg-slate-50
                flex flex-col lg:overflow-y-auto">

        {{-- ── Mobile / Tablet sticky header ─────────────────── --}}
        <header class="lg:hidden sticky top-0 z-10 bg-white border-b border-slate-100
                       px-4 sm:px-6 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('SVG.png') }}" alt="Qimta" class="h-8 w-8 object-contain">
                <span class="text-slate-900 text-base font-bold tracking-tight">Qimta</span>
            </div>
            @yield('mobile-header-action')
        </header>

        {{-- ── Desktop top-right action ─── --}}
        <div class="hidden lg:flex justify-end px-8 xl:px-12 pt-8 pb-0 shrink-0">
            @yield('desktop-header-action')
        </div>

        {{-- ── Form area – vertically centred ─────────────────── --}}
        <div class="flex-1 flex items-center justify-center
                    px-4 sm:px-6 py-8 lg:py-4">

            <div class="w-full sm:max-w-md lg:max-w-sm xl:max-w-md">

                {{-- Card shell (mobile/tablet only) --}}
                <div class="bg-white lg:bg-transparent
                            rounded-2xl lg:rounded-none
                            shadow-sm lg:shadow-none
                            px-6 sm:px-8 lg:px-0
                            py-8 sm:py-9 lg:py-0">

                    @yield('form')

                </div>
            </div>
        </div>

        {{-- ── Footer ───────────────────────────────────────────── --}}
        <footer class="shrink-0 px-6 lg:px-10 py-4 flex flex-wrap items-center
                       justify-center lg:justify-between gap-2
                       border-t border-slate-100 bg-white lg:bg-transparent">
            <span class="text-xs text-slate-400">© {{ date('Y') }} Qimta. All rights reserved.</span>
            <div class="flex items-center gap-4">
                <a href="#" class="text-xs text-slate-400 hover:text-emerald-600 transition">Privacy Policy</a>
                <span class="text-slate-300 text-xs">|</span>
                <a href="#" class="text-xs text-slate-400 hover:text-emerald-600 transition">Terms & Conditions</a>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
