<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Qimta')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ══════════════════════════════════════════════════════════
         MOBILE SIDEBAR OVERLAY
    ══════════════════════════════════════════════════════════ --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-black/50 lg:hidden"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    {{-- ══════════════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════════════ --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-slate-200 flex flex-col
               transition-transform duration-300 ease-in-out
               lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-200">
            <div class="flex items-center justify-center w-9 h-9 bg-emerald-600 rounded-lg shrink-0">
                <img src="{{ asset('SVG.png') }}" alt="Qimta" class="h-5 w-5 object-contain brightness-0 invert">
            </div>
            <span class="text-slate-900 text-lg font-bold tracking-tight">Qimta</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
            {{-- Dashboard --}}
            <a href="{{ route('enduser.dashboard') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.dashboard')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Quotations --}}
            <a href="{{ route('enduser.quotations.index') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.quotations*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Quotations</span>
            </a>

            {{-- BOQ --}}
            <a href="{{ route('enduser.boqs.index') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.boqs*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>BOQ</span>
            </a>

            {{-- Orders --}}
            <a href="{{ route('enduser.orders.index') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.orders*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Orders</span>
            </a>

            {{-- Projects --}}
            <a href="#"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.projects*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span>Projects</span>
            </a>

            {{-- Reports --}}
            <a href="#"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.reports*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Reports</span>
            </a>
        </nav>

        {{-- Bottom user info --}}
        <div class="px-3 pb-5 pt-3 border-t border-slate-200">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl
                               text-slate-600 hover:bg-slate-100
                               transition-all duration-150">
                    @php
                        use App\Helpers\ImageHelper;
                        $sidebarAvatarUrl = auth()->check() ? ImageHelper::avatarUrl(auth()->user()->avatar, auth()->user()->name ?? 'U') : '';
                    @endphp
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 text-white font-semibold text-xs overflow-hidden"
                         x-data="{ err: false }">
                        @if ($sidebarAvatarUrl)
                            <img src="{{ $sidebarAvatarUrl }}" alt="" class="w-full h-full object-cover"
                                 x-show="!err" @@error="err = true">
                            <span x-show="err" x-cloak>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</span>
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        @endif
                    </div>
                    <div class="flex-1 text-left min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <svg class="w-4 h-4 shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden">
                    <a href="{{ route('enduser.logout') }}"
                       class="flex items-center gap-2.5 px-4 py-3 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign out
                    </a>
                </div>
            </div>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════════════
         MAIN WRAPPER (pushes right of sidebar)
    ══════════════════════════════════════════════════════════ --}}
    <div class="lg:ml-64 flex flex-col min-h-screen">

        {{-- ── TOP NAVBAR ─────────────────────────────────────── --}}
        <header class="sticky top-0 z-10 bg-white border-b border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-4 px-4 sm:px-6 h-16">

                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = true"
                        class="lg:hidden p-2 -ml-1 text-slate-500 hover:text-slate-700
                               hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title / breadcrumb --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-base sm:text-lg font-semibold text-slate-900 truncate">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    @hasSection('breadcrumb')
                    <nav class="flex items-center gap-1.5 mt-0.5">
                        @yield('breadcrumb')
                    </nav>
                    @endif
                </div>

                {{-- Right actions --}}
                <div class="flex items-center gap-2 sm:gap-3">

                    {{-- Search --}}
                    <div class="hidden sm:flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 w-48 lg:w-60">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input type="text" placeholder="Search..."
                               class="bg-transparent text-sm text-slate-700 placeholder-slate-400
                                      focus:outline-none w-full">
                    </div>

                    {{-- Notifications --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="relative p-2 text-slate-500 hover:text-slate-700
                                       hover:bg-slate-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl
                                    border border-slate-200 shadow-xl overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-900">Notifications</span>
                                <span class="text-xs bg-red-100 text-red-600 font-medium px-2 py-0.5 rounded-full">3 new</span>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                                <a href="#" class="flex gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">Quotation accepted</p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">Your quotation #QT-0042 has been accepted.</p>
                                        <p class="text-xs text-slate-400 mt-1">2 min ago</p>
                                    </div>
                                </a>
                                <a href="#" class="flex gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">Order update</p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">Order #ORD-0018 is now in transit.</p>
                                        <p class="text-xs text-slate-400 mt-1">1 hour ago</p>
                                    </div>
                                </a>
                                <a href="#" class="flex gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">Action required</p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">Please review your project milestone.</p>
                                        <p class="text-xs text-slate-400 mt-1">3 hours ago</p>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-3 border-t border-slate-100">
                                <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                    View all notifications →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Settings --}}



                    {{-- User avatar --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg
                                       hover:bg-slate-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center
                                        text-white font-semibold text-xs shrink-0 overflow-hidden"
                                 x-data="{ err: false }">
                                @if (auth()->check() && auth()->user()->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->avatar))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->avatar) }}" alt="" class="w-full h-full object-cover"
                                         x-show="!err" @@error="err = true">
                                    <span x-show="err" x-cloak>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</span>
                                @else
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                @endif
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-slate-700 max-w-[120px] truncate">
                                {{ auth()->user()->name ?? 'User' }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl
                                    border border-slate-200 shadow-xl overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('enduser.profile') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    My Profile
                                </a>

                            </div>
                            <div class="border-t border-slate-100 py-1">
                                <a href="{{ route('enduser.logout') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── PAGE CONTENT ─────────────────────────────────── --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

        {{-- ── FOOTER ────────────────────────────────────────── --}}
        <footer class="px-4 sm:px-6 lg:px-8 py-4 border-t border-slate-200 bg-white
                        flex flex-wrap items-center justify-between gap-2">
            <span class="text-xs text-slate-400">© {{ date('Y') }} Qimta. All rights reserved.</span>
            <div class="flex items-center gap-4">
                <a href="#" class="text-xs text-slate-400 hover:text-emerald-600 transition">Privacy Policy</a>
                <span class="text-slate-300 text-xs">|</span>
                <a href="#" class="text-xs text-slate-400 hover:text-emerald-600 transition">Terms & Conditions</a>
            </div>
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
