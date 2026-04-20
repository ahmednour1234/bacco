<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('app.supplier_portal_title'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        @media (min-width: 1024px) {
            .sidebar-offset { margin-inline-start: 16rem; }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/50 lg:hidden"
        x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'"
        class="fixed inset-y-0 start-0 z-30 w-64 bg-white border-e border-slate-200 flex flex-col
               transition-transform duration-300 ease-in-out
               lg:translate-x-0">
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-200">
            <div class="flex items-center justify-center w-9 h-9 bg-emerald-600 rounded-lg shrink-0">
                <img src="{{ asset('SVG.png') }}" alt="Qimta" class="h-5 w-5 object-contain brightness-0 invert">
            </div>
            <div>
                <span class="block text-slate-900 text-lg font-bold tracking-tight leading-none">Qimta</span>
                <p class="text-xs text-slate-400 font-medium mt-0.5">{{ __('app.supplier_portal') }}</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
            {{-- Dashboard --}}
            <a href="{{ route('supplier.dashboard') }}" wire:navigate
                class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('supplier.dashboard')
                          ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                          : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ __('app.dashboard') }}</span>
            </a>

            {{-- My Products --}}
            <a href="{{ route('supplier.products.index') }}" wire:navigate
                class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('supplier.products*')
                          ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                          : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>{{ __('app.my_products') }}</span>
            </a>

            {{-- My Profile --}}

        </nav>

        {{-- Bottom user info --}}
        <div class="px-3 pb-5 pt-3 border-t border-slate-200">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl
                               text-slate-600 hover:bg-slate-100
                               transition-all duration-150">
                    <div
                        class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 text-white font-semibold text-xs">
                        {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 2)) }}
                    </div>
                    <div class="flex-1 text-left min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name ?? 'Supplier' }}
                        </p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <svg class="w-4 h-4 shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                    <a href="{{ route('supplier.profile') }}" wire:navigate
                        class="flex items-center gap-2.5 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('app.my_profile') }}
                    </a>
                    <div class="border-t border-slate-100"></div>
                    <a href="{{ route('supplier.logout') }}"
                        class="flex items-center gap-2.5 px-4 py-3 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {{ __('app.sign_out') }}
                    </a>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="sidebar-offset flex flex-col min-h-screen">

        {{-- Top navbar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-4 px-4 sm:px-6 h-16">

                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = true"
                    class="lg:hidden p-2 -ml-1 text-slate-500 hover:text-slate-700
                               hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
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

                    {{-- Notifications --}}
                    @livewire('notification-dropdown')

                    {{-- Language Switcher --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm text-slate-600
                                       hover:bg-slate-100 rounded-lg transition-colors">
                            <span>{{ app()->getLocale() === 'ar' ? '🇸🇦' : '🇺🇸' }}</span>
                            <span class="hidden sm:inline text-xs font-medium uppercase">{{ app()->getLocale() }}</span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} top-full mt-2 w-36 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden z-50">
                            <a href="{{ route('locale.switch', 'en') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ app()->getLocale() === 'en' ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-slate-700 hover:bg-slate-50' }} transition-colors">
                                🇺🇸 English
                            </a>
                            <a href="{{ route('locale.switch', 'ar') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ app()->getLocale() === 'ar' ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-slate-700 hover:bg-slate-50' }} transition-colors">
                                🇸🇦 العربية
                            </a>
                        </div>
                    </div>

                    {{-- User avatar --}}
                    <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg
                                   hover:bg-slate-100 transition-colors">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center
                                    text-white font-semibold text-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 2)) }}
                        </div>
                        <span
                            class="hidden sm:block text-sm font-medium text-slate-700">{{ auth()->user()->name ?? __('app.supplier') }}</span>
                        <svg class="w-4 h-4 text-slate-400" :class="open ? 'rotate-180' : ''" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} top-full mt-2 w-48 bg-white rounded-xl
                                border border-slate-200 shadow-xl overflow-hidden z-50">
                        <a href="{{ route('supplier.profile') }}" wire:navigate
                            class="flex items-center gap-2.5 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('app.my_profile') }}
                        </a>
                        <div class="border-t border-slate-100"></div>
                        <a href="{{ route('supplier.logout') }}"
                            class="flex items-center gap-2.5 px-4 py-3 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ __('app.sign_out') }}
                        </a>
                    </div>
                </div>
                </div>{{-- end right actions --}}
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>

</html>
