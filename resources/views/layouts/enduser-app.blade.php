<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Qimta')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        html { overflow-x: clip; }
        @media (min-width: 1024px) {
            .sidebar-offset { margin-inline-start: 16rem; }
        }
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
        :class="sidebarOpen ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'"
        class="fixed inset-y-0 start-0 z-30 w-64 bg-white border-e border-slate-200 flex flex-col
               transition-transform duration-300 ease-in-out
               lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex items-center px-6 py-5 border-b border-slate-200">
            <a href="{{ route('enduser.dashboard') }}">
                <x-logo class="h-14 w-auto" />
            </a>
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
                <span>{{ __('app.dashboard') }}</span>
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
                <span>{{ __('app.quotations') }}</span>
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
                <span>{{ __('app.orders') }}</span>
            </a>

            {{-- Payments --}}
            @php
                $pendingPaymentsCount = auth()->check()
                    ? \App\Models\Payment::where('client_id', auth()->id())
                        ->whereIn('status', ['pending','submitted'])
                        ->count()
                    : 0;
            @endphp
            <a href="{{ route('enduser.payments.index') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.payments*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'المدفوعات' : 'Payments' }}</span>
                @if($pendingPaymentsCount > 0)
                    <span class="{{ request()->routeIs('enduser.payments*') ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-700' }} ms-auto text-xs font-bold rounded-full px-2 py-0.5">
                        {{ $pendingPaymentsCount }}
                    </span>
                @endif
            </a>

            {{-- Projects --}}
            <a href="{{ route('enduser.projects.index') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.projects*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span>{{ __('app.projects') }}</span>
            </a>

            {{-- Reports --}}
            <a href="{{ route('enduser.reports.index') }}" wire:navigate
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('enduser.reports*')
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>{{ __('app.reports') }}</span>
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
                        {{ __('app.sign_out') }}
                    </a>
                </div>
            </div>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════════════
         MAIN WRAPPER (pushes right of sidebar)
    ══════════════════════════════════════════════════════════ --}}
    <div class="sidebar-offset flex flex-col min-h-screen">

        {{-- ── TOP NAVBAR ─────────────────────────────────────── --}}
        <header class="sticky top-0 z-40 bg-white border-b border-slate-200/80 shadow-sm">
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

                    {{-- Language Switcher --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm text-slate-600
                                       hover:bg-slate-100 rounded-lg transition-colors">
                            <span>{{ app()->getLocale() === 'ar' ? '🇸🇦' : '🇺🇸' }}</span>
                            <span class="hidden sm:inline text-xs font-medium uppercase">{{ app()->getLocale() }}</span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                             class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} top-full mt-2 w-36 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden z-50">
                            <a href="{{ route('locale.switch', 'en') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ app()->getLocale() === 'en' ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-slate-700 hover:bg-slate-50' }} transition-colors">
                                <span>🇺🇸</span> {{ __('app.english') }}
                            </a>
                            <a href="{{ route('locale.switch', 'ar') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ app()->getLocale() === 'ar' ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-slate-700 hover:bg-slate-50' }} transition-colors">
                                <span>🇸🇦</span> {{ __('app.arabic') }}
                            </a>
                        </div>
                    </div>

                    {{-- Notifications --}}
                    @livewire('notification-dropdown')

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
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute end-0 top-full mt-2 w-64
                                    bg-white
                                    rounded-2xl border border-slate-200
                                    shadow-xl z-50">

                            {{-- ── Identity ──────────────────────────────────── --}}
                            <div class="flex items-center gap-3.5 px-4 pt-4 pb-3.5">
                                <div class="relative shrink-0">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500
                                                flex items-center justify-center text-white font-bold text-sm
                                                shadow-md shadow-emerald-500/30 overflow-hidden"
                                         x-data="{ err: false }">
                                        @if (auth()->check() && auth()->user()->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->avatar))
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->avatar) }}" alt="" class="w-full h-full object-cover"
                                                 x-show="!err" @@error="err = true">
                                            <span x-show="err" x-cloak>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</span>
                                        @else
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                        @endif
                                    </div>
                                    <span class="absolute -bottom-0.5 -end-0.5 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-900 truncate leading-tight">{{ auth()->user()->name ?? 'User' }}</p>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5 leading-tight">{{ auth()->user()->email ?? '' }}</p>
                                </div>
                            </div>

                            {{-- ── Divider ─────────────────────────────────────── --}}
                            <div class="mx-4 border-t border-slate-100"></div>

                            {{-- ── Menu Items ──────────────────────────────────── --}}
                            <div class="px-2 py-2">
                                <a href="{{ route('enduser.profile') }}" wire:navigate @click="open = false"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group
                                          text-slate-600 hover:bg-slate-50/80 hover:text-slate-900">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-white group-hover:shadow-sm
                                                flex items-center justify-center transition-all duration-150 shrink-0
                                                ring-1 ring-transparent group-hover:ring-slate-200">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium flex-1">{{ __('app.my_profile') }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-300 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>

                                <div class="my-1 mx-1 border-t border-slate-100/80"></div>

                                <a href="{{ route('enduser.logout') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group
                                          text-red-500 hover:bg-red-50/70">
                                    <div class="w-8 h-8 rounded-xl bg-red-50 group-hover:bg-white group-hover:shadow-sm
                                                flex items-center justify-center transition-all duration-150 shrink-0
                                                ring-1 ring-transparent group-hover:ring-red-100">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ __('app.sign_out') }}</span>
                                </a>
                            </div>

                            {{-- Bottom breathing room --}}
                            <div class="h-2"></div>
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
            <span class="text-xs text-slate-400">© {{ date('Y') }} Qimta. {{ __('app.all_rights') }}</span>
            <div class="flex items-center gap-4">
                <a href="#" class="text-xs text-slate-400 hover:text-emerald-600 transition">{{ __('app.privacy_policy') }}</a>
                <span class="text-slate-300 text-xs">|</span>
                <a href="#" class="text-xs text-slate-400 hover:text-emerald-600 transition">{{ __('app.terms') }}</a>
            </div>
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')

    {{-- ── Persistent background-job pill (survives wire:navigate) ── --}}
    {{-- @assets guarantees this block runs only ONCE per browser session, never re-run by wire:navigate --}}
    @assets
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('bgJob', { active: false, done: null });
        });

        document.addEventListener('livewire:navigated', () => {
            if (window.location.pathname.includes('/boqs/create')) {
                if (window.Alpine && Alpine.store('bgJob')) {
                    Alpine.store('bgJob').done = null;
                }
            }
        });

        window._bgPollTimer = null;
        window._bgPollStart = function() {
            if (window._bgPollTimer) return;
            window._bgPollTimer = setInterval(async () => {
                if (!window.Alpine || !Alpine.store('bgJob').active) {
                    window._bgPollStop();
                    return;
                }
                try {
                    const res  = await fetch('{{ route('enduser.boqs.draft-status') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    const onCreatePage = window.location.pathname.includes('/boqs/create');

                    if (data.ai_status === 'done' || data.items_count > 0) {
                        window._bgPollStop();
                        Alpine.store('bgJob').active = false;
                        if (!onCreatePage) Alpine.store('bgJob').done = 'success';
                    } else if (data.ai_status === 'failed' || data.ai_status === 'no_items') {
                        window._bgPollStop();
                        Alpine.store('bgJob').active = false;
                        if (!onCreatePage) Alpine.store('bgJob').done = data.ai_status;
                    }
                } catch (e) { /* ignore */ }
            }, 4000);
        };
        window._bgPollStop = function() {
            clearInterval(window._bgPollTimer);
            window._bgPollTimer = null;
        };

        document.addEventListener('alpine:initialized', () => {
            Alpine.effect(() => {
                Alpine.store('bgJob').active ? window._bgPollStart() : window._bgPollStop();
            });
        });
    </script>
    @endassets

    <div
        x-data="{ isAr: document.documentElement.dir === 'rtl' }"
        x-show="$store.bgJob.active"
        x-cloak
        x-on:boq-upload-done.window="$store.bgJob.active = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:99999;pointer-events:auto;"
    >
        <div
            style="background:#0f172a;color:#fff;border-radius:99px;padding:10px 20px;display:flex;align-items:center;gap:10px;font-family:'Cairo',sans-serif;font-size:0.82rem;font-weight:600;box-shadow:0 8px 30px rgba(0,0,0,0.25);white-space:nowrap;"
        >
            {{-- Clickable area → navigate back to BOQ create (resumes latest draft) --}}
            <a
                href="{{ route('enduser.boqs.create') }}?resume=1"
                wire:navigate
                style="display:flex;align-items:center;gap:10px;color:#fff;text-decoration:none;"
            >
                <svg style="width:14px;height:14px;animation:gcw_pill 1.2s linear infinite;flex-shrink:0;" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke="#34d399" stroke-width="3" stroke-dasharray="40 20" stroke-linecap="round"/>
                </svg>
                <span x-text="isAr ? 'العملية جارية… اضغط للرجوع' : 'Processing… tap to return'"></span>
            </a>
            <button
                @click.stop="$store.bgJob.active = false"
                style="margin-inline-start:6px;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;flex-shrink:0;"
            >
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
    <style>@keyframes gcw_pill { to { transform: rotate(360deg); } }</style>

    {{-- ── BOQ done popup (shows on any page when processing completes) ── --}}
    <div
        x-data="{ isAr: document.documentElement.dir === 'rtl' }"
        x-show="$store.bgJob.done !== null"
        x-cloak
        x-on:boq-upload-done.window="if (!window.location.pathname.includes('/boqs/create') && !window.location.pathname.includes('/boqs/create/')) $store.bgJob.done = 'success'; $store.bgJob.active = false;"
        x-on:boq-resume-done.window="$store.bgJob.active = false; $store.bgJob.done = null"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:999999;pointer-events:none;"
    >
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            style="pointer-events:auto;background:#fff;border-radius:24px;padding:36px 40px;box-shadow:0 20px 60px rgba(0,0,0,0.18);text-align:center;max-width:380px;width:90%;"
        >
            {{-- Success --}}
            <template x-if="$store.bgJob.done === 'success'">
                <div>
                    <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <svg width="28" height="28" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <p style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:8px;font-family:'Cairo',sans-serif;" x-text="isAr ? 'اكتملت المعالجة بنجاح' : 'Processing Complete'"></p>
                    <p style="font-size:0.85rem;color:#64748b;margin-bottom:24px;font-family:'Cairo',sans-serif;" x-text="isAr ? 'تم استخراج العناصر بنجاح، اضغط لمراجعتها' : 'Items extracted. Tap to review.'"></p>
                    <a href="{{ route('enduser.boqs.create') }}?resume=1" wire:navigate @click="$store.bgJob.done = null" style="display:block;background:#10b981;color:#fff;border-radius:14px;padding:12px 20px;font-size:0.9rem;font-weight:700;font-family:'Cairo',sans-serif;text-decoration:none;" x-text="isAr ? 'عرض البيانات ←' : '→ View Data'"></a>
                </div>
            </template>

            {{-- Failed --}}
            <template x-if="$store.bgJob.done === 'failed'">
                <div>
                    <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <svg width="28" height="28" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <p style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:8px;font-family:'Cairo',sans-serif;" x-text="isAr ? 'حدثت مشكلة أثناء المعالجة' : 'Processing Failed'"></p>
                    <p style="font-size:0.85rem;color:#64748b;margin-bottom:24px;font-family:'Cairo',sans-serif;" x-text="isAr ? 'فشل استخراج العناصر. يمكنك إضافتها يدوياً أو إعادة المحاولة.' : 'Item extraction failed. You can add items manually or retry.'"></p>
                    <a href="{{ route('enduser.boqs.create') }}?resume=1" wire:navigate @click="$store.bgJob.done = null" style="display:block;background:#ef4444;color:#fff;border-radius:14px;padding:12px 20px;font-size:0.9rem;font-weight:700;font-family:'Cairo',sans-serif;text-decoration:none;" x-text="isAr ? 'الرجوع وإضافة يدوياً ←' : '→ Add Manually'"></a>
                </div>
            </template>

            {{-- No items --}}
            <template x-if="$store.bgJob.done === 'no_items'">
                <div>
                    <div style="width:56px;height:56px;border-radius:50%;background:#fef9c3;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <svg width="28" height="28" fill="none" stroke="#ca8a04" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <p style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:8px;font-family:'Cairo',sans-serif;" x-text="isAr ? 'لم يتم استخراج عناصر' : 'No Items Found'"></p>
                    <p style="font-size:0.85rem;color:#64748b;margin-bottom:24px;font-family:'Cairo',sans-serif;" x-text="isAr ? 'لم يتمكن الذكاء الاصطناعي من قراءة الملف. يمكنك إضافة العناصر يدوياً.' : 'The AI could not read the file. Add items manually.'"></p>
                    <a href="{{ route('enduser.boqs.create') }}?resume=1" wire:navigate @click="$store.bgJob.done = null" style="display:block;background:#f59e0b;color:#fff;border-radius:14px;padding:12px 20px;font-size:0.9rem;font-weight:700;font-family:'Cairo',sans-serif;text-decoration:none;" x-text="isAr ? 'إضافة يدوياً ←' : '→ Add Manually'"></a>
                </div>
            </template>

            <button @click="$store.bgJob.done = null" style="margin-top:12px;width:100%;background:transparent;color:#94a3b8;border:none;font-size:0.8rem;cursor:pointer;font-family:'Cairo',sans-serif;" x-text="isAr ? 'إغلاق' : 'Dismiss'"></button>
        </div>
    </div>

</body>
</html>
