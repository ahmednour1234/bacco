@extends('layouts.enduser-app')

@section('title', __('app.dashboard') . ' - Qimta')
@section('page-title', __('app.dashboard'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.dashboard') }}</span>
@endsection

@section('content')

{{-- Promo Banner Slider --}}
<div x-data="{
        slide: 0,
        total: 3,
        timer: null,
        start() { this.timer = setInterval(() => { this.slide = (this.slide + 1) % this.total }, 4500) },
        go(n) { this.slide = n; clearInterval(this.timer); this.start() }
     }"
     x-init="start()"
     class="relative mb-6 rounded-2xl overflow-hidden select-none"
     style="height:170px;">

    {{-- ── Slide 1 – Create BOQ (violet) ───────────────────────────────── --}}
    <div x-show="slide === 0"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 flex items-center"
         style="background:linear-gradient(120deg,#ede9fe 0%,#c4b5fd 55%,#8b5cf6 100%);">
        {{-- sparkle dots --}}
        <span class="absolute" style="top:18px;left:52%;font-size:14px;color:#f472b6;pointer-events:none;">&#10022;</span>
        <span class="absolute" style="bottom:20px;left:48%;font-size:9px;color:#2dd4bf;pointer-events:none;">&#10022;</span>
        <span class="absolute" style="top:30px;left:55%;font-size:9px;color:#f9a8d4;pointer-events:none;">+</span>
        <span class="absolute" style="bottom:14px;left:56%;font-size:9px;color:#c4b5fd;pointer-events:none;">&#9670;</span>
        {{-- text --}}
        <div class="flex-1 px-8 py-6">
            <p class="text-xs font-bold text-violet-600 uppercase tracking-widest mb-1">{{ __('app.banner_free_badge') }}</p>
            <h2 class="text-2xl sm:text-3xl font-black text-violet-950 leading-tight mb-2">{{ __('app.banner_title') }}</h2>
            <p class="text-sm text-violet-700 font-medium mb-1">{{ __('app.banner_subtitle') }}</p>
            <p class="text-xs text-violet-500 font-semibold">{{ __('app.banner_tagline') }}</p>
        </div>
        {{-- CTA --}}
        <div class="flex-shrink-0 px-6">
            <a href="{{ route('enduser.boqs.create') }}"
               class="inline-flex items-center gap-2 bg-violet-900 hover:bg-violet-800 text-white text-sm font-bold px-7 py-3.5 rounded-xl shadow-lg transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('app.banner_cta') }}
            </a>
        </div>
        {{-- illustration --}}
        <div class="hidden lg:flex flex-shrink-0 self-end items-end" style="width:210px;height:170px;overflow:hidden;">
            <svg viewBox="0 0 210 170" style="width:210px;height:170px;" xmlns="http://www.w3.org/2000/svg">
                <text x="10" y="24" font-size="14" fill="#f472b6">&#10022;</text>
                <text x="188" y="28" font-size="10" fill="#2dd4bf">&#10022;</text>
                <text x="193" y="155" font-size="8" fill="#a78bfa">&#9670;</text>
                <rect x="16" y="40" width="76" height="116" rx="10" fill="#7c3aed" opacity="0.9"/>
                <rect x="24" y="50" width="60" height="24" rx="5" fill="#a78bfa"/>
                <rect x="28" y="55" width="52" height="14" rx="3" fill="#ede9fe"/>
                <rect x="26" y="82" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="42" y="82" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="58" y="82" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="74" y="82" width="12" height="9" rx="2" fill="#8b5cf6"/>
                <rect x="26" y="95" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="42" y="95" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="58" y="95" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="74" y="95" width="12" height="22" rx="2" fill="#8b5cf6"/>
                <rect x="26" y="108" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="42" y="108" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="58" y="108" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="26" y="121" width="28" height="9" rx="2" fill="#6d28d9"/>
                <rect x="58" y="121" width="12" height="9" rx="2" fill="#c4b5fd"/>
                <rect x="68" y="14" width="108" height="142" rx="10" fill="white" opacity="0.97"/>
                <rect x="104" y="6" width="36" height="16" rx="5" fill="#7c3aed"/>
                <rect x="110" y="2" width="24" height="12" rx="4" fill="#5b21b6"/>
                <rect x="80" y="38" width="84" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="80" y="50" width="84" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="80" y="62" width="64" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="80" y="74" width="84" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="80" y="86" width="50" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="80" y="98" width="84" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="80" y="110" width="40" height="5" rx="2" fill="#e2e8f0"/>
                <rect x="108" y="126" width="48" height="20" rx="6" fill="#22c55e"/>
                <text x="132" y="140" text-anchor="middle" font-size="8" font-weight="900" fill="white" font-family="Arial,sans-serif">FREE</text>
            </svg>
        </div>
    </div>

    {{-- ── Slide 2 – Get Quotation Fast (emerald) ──────────────────────── --}}
    <div x-show="slide === 1"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 flex items-center"
         style="background:linear-gradient(120deg,#d1fae5 0%,#6ee7b7 50%,#059669 100%);">
        <span class="absolute" style="top:16px;left:52%;font-size:14px;color:#fbbf24;pointer-events:none;">&#10022;</span>
        <span class="absolute" style="bottom:22px;left:49%;font-size:9px;color:#fff;pointer-events:none;">&#10022;</span>
        <span class="absolute" style="top:32px;left:56%;font-size:9px;color:#a7f3d0;pointer-events:none;">+</span>
        <span class="absolute" style="bottom:14px;left:55%;font-size:9px;color:#6ee7b7;pointer-events:none;">&#9670;</span>
        {{-- text --}}
        <div class="flex-1 px-8 py-6">
            <p class="text-xs font-bold text-emerald-700 uppercase tracking-widest mb-1">{{ __('app.banner2_badge') }}</p>
            <h2 class="text-2xl sm:text-3xl font-black text-emerald-950 leading-tight mb-2">{{ __('app.banner2_title') }}</h2>
            <p class="text-sm text-emerald-800 font-medium mb-1">{{ __('app.banner2_subtitle') }}</p>
            <p class="text-xs text-emerald-700 font-semibold">{{ __('app.banner2_tagline') }}</p>
        </div>
        {{-- CTA --}}
        <div class="flex-shrink-0 px-6">
            <a href="{{ route('enduser.quotations.create') }}"
               class="inline-flex items-center gap-2 bg-emerald-900 hover:bg-emerald-800 text-white text-sm font-bold px-7 py-3.5 rounded-xl shadow-lg transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                {{ __('app.banner2_cta') }}
            </a>
        </div>
        {{-- illustration --}}
        <div class="hidden lg:flex flex-shrink-0 self-end items-end" style="width:210px;height:170px;overflow:hidden;">
            <svg viewBox="0 0 210 170" style="width:210px;height:170px;" xmlns="http://www.w3.org/2000/svg">
                <text x="10" y="24" font-size="14" fill="#fbbf24">&#10022;</text>
                <text x="188" y="28" font-size="10" fill="#fff">&#10022;</text>
                {{-- document stack --}}
                <rect x="30" y="60" width="90" height="110" rx="8" fill="#059669" opacity="0.3"/>
                <rect x="40" y="45" width="90" height="110" rx="8" fill="#065f46" opacity="0.5"/>
                <rect x="50" y="30" width="90" height="130" rx="8" fill="white" opacity="0.96"/>
                <rect x="80" y="24" width="30" height="12" rx="4" fill="#059669"/>
                <rect x="62" y="52" width="66" height="5" rx="2" fill="#d1fae5"/>
                <rect x="62" y="64" width="66" height="5" rx="2" fill="#d1fae5"/>
                <rect x="62" y="76" width="50" height="5" rx="2" fill="#d1fae5"/>
                <rect x="62" y="88" width="66" height="5" rx="2" fill="#d1fae5"/>
                <rect x="62" y="100" width="40" height="5" rx="2" fill="#d1fae5"/>
                {{-- checkmark --}}
                <circle cx="115" cy="125" r="22" fill="#059669"/>
                <polyline points="105,125 113,133 127,116" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                {{-- clock --}}
                <circle cx="62" cy="130" r="18" fill="#fbbf24"/>
                <circle cx="62" cy="130" r="14" fill="white"/>
                <line x1="62" y1="119" x2="62" y2="130" stroke="#374151" stroke-width="2" stroke-linecap="round"/>
                <line x1="62" y1="130" x2="70" y2="136" stroke="#374151" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    {{-- ── Slide 3 – Track Your Orders (blue) ──────────────────────────── --}}
    <div x-show="slide === 2"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 flex items-center"
         style="background:linear-gradient(120deg,#dbeafe 0%,#93c5fd 50%,#2563eb 100%);">
        <span class="absolute" style="top:16px;left:52%;font-size:14px;color:#f472b6;pointer-events:none;">&#10022;</span>
        <span class="absolute" style="bottom:22px;left:49%;font-size:9px;color:#fbbf24;pointer-events:none;">&#10022;</span>
        <span class="absolute" style="top:32px;left:56%;font-size:9px;color:#bfdbfe;pointer-events:none;">+</span>
        <span class="absolute" style="bottom:14px;left:55%;font-size:9px;color:#93c5fd;pointer-events:none;">&#9670;</span>
        {{-- text --}}
        <div class="flex-1 px-8 py-6">
            <p class="text-xs font-bold text-blue-700 uppercase tracking-widest mb-1">{{ __('app.banner3_badge') }}</p>
            <h2 class="text-2xl sm:text-3xl font-black text-blue-950 leading-tight mb-2">{{ __('app.banner3_title') }}</h2>
            <p class="text-sm text-blue-800 font-medium mb-1">{{ __('app.banner3_subtitle') }}</p>
            <p class="text-xs text-blue-700 font-semibold">{{ __('app.banner3_tagline') }}</p>
        </div>
        {{-- CTA --}}
        <div class="flex-shrink-0 px-6">
            <a href="{{ route('enduser.orders.index') }}"
               class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold px-7 py-3.5 rounded-xl shadow-lg transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                {{ __('app.banner3_cta') }}
            </a>
        </div>
        {{-- illustration --}}
        <div class="hidden lg:flex flex-shrink-0 self-end items-end" style="width:210px;height:170px;overflow:hidden;">
            <svg viewBox="0 0 210 170" style="width:210px;height:170px;" xmlns="http://www.w3.org/2000/svg">
                <text x="10" y="24" font-size="14" fill="#f472b6">&#10022;</text>
                <text x="188" y="28" font-size="10" fill="#fbbf24">&#10022;</text>
                {{-- phone/app mockup --}}
                <rect x="60" y="18" width="80" height="142" rx="14" fill="#1e3a8a" opacity="0.9"/>
                <rect x="66" y="28" width="68" height="122" rx="8" fill="#dbeafe"/>
                <circle cx="100" cy="22" r="4" fill="#3b82f6" opacity="0.6"/>
                {{-- app rows --}}
                <rect x="72" y="38" width="56" height="10" rx="3" fill="#2563eb"/>
                <rect x="72" y="54" width="40" height="6" rx="2" fill="#93c5fd"/>
                <rect x="72" y="64" width="56" height="6" rx="2" fill="#bfdbfe"/>
                <rect x="72" y="78" width="40" height="6" rx="2" fill="#93c5fd"/>
                <rect x="72" y="88" width="56" height="6" rx="2" fill="#bfdbfe"/>
                <rect x="72" y="102" width="40" height="6" rx="2" fill="#93c5fd"/>
                <rect x="72" y="112" width="56" height="6" rx="2" fill="#bfdbfe"/>
                {{-- status dot --}}
                <circle cx="84" cy="57" r="3" fill="#22c55e"/>
                <circle cx="84" cy="81" r="3" fill="#f59e0b"/>
                <circle cx="84" cy="105" r="3" fill="#3b82f6"/>
                {{-- box icon floating --}}
                <rect x="140" y="60" width="50" height="50" rx="10" fill="#2563eb" opacity="0.85"/>
                <path d="M155 82 L165 76 L175 82 L175 94 L165 100 L155 94 Z" stroke="white" stroke-width="2" fill="none" stroke-linejoin="round"/>
                <line x1="165" y1="76" x2="165" y2="100" stroke="white" stroke-width="1.5"/>
                <line x1="155" y1="82" x2="175" y2="82" stroke="white" stroke-width="1.5"/>
            </svg>
        </div>
    </div>

    {{-- Dot indicators --}}
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2" style="z-index:10;">
        <template x-for="i in total" :key="i">
            <button @click="go(i-1)"
                    class="transition-all duration-300 rounded-full"
                    :class="slide === i-1 ? 'w-6 h-2.5 bg-white shadow' : 'w-2.5 h-2.5 bg-white/40 hover:bg-white/70'">
            </button>
        </template>
    </div>

</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-7">

    {{-- Total Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}"
       class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 flex flex-col justify-between overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-2xl"></div>
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <svg class="w-20 h-10" viewBox="0 0 80 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="g-blue" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M 0,30 C 8,28 14,24 22,20 C 30,16 30,14 38,13 C 46,12 46,16 52,16 C 58,16 60,10 68,7 L 80,4 L 80,36 L 0,36 Z" fill="url(#g-blue)"/>
                <path d="M 0,30 C 8,28 14,24 22,20 C 30,16 30,14 38,13 C 46,12 46,16 52,16 C 58,16 60,10 68,7 L 80,4" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div class="mt-3">
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $stats['total_quotations'] }}</p>
            <p class="text-xs font-medium text-slate-500 mt-1">{{ __('app.total_quotations') }}</p>
        </div>
    </a>

    {{-- Active Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}"
       class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 flex flex-col justify-between overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-50/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-2xl"></div>
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
            </div>
            <svg class="w-20 h-10" viewBox="0 0 80 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="g-amber" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#f59e0b" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M 0,24 C 8,23 10,20 14,20 C 18,20 22,24 28,24 C 34,24 36,14 44,13 C 52,12 54,18 60,18 C 66,18 68,10 76,10 L 80,11 L 80,36 L 0,36 Z" fill="url(#g-amber)"/>
                <path d="M 0,24 C 8,23 10,20 14,20 C 18,20 22,24 28,24 C 34,24 36,14 44,13 C 52,12 54,18 60,18 C 66,18 68,10 76,10 L 80,11" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div class="mt-3">
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $stats['active_quotations'] }}</p>
            <p class="text-xs font-medium text-slate-500 mt-1">{{ __('app.active_quotations') }}</p>
        </div>
    </a>

    {{-- Active Orders --}}
    <a href="{{ route('enduser.orders.index') }}"
       class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 flex flex-col justify-between overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-2xl"></div>
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <svg class="w-20 h-10" viewBox="0 0 80 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="g-emerald" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M 0,30 C 10,28 14,25 18,24 C 24,23 26,22 34,21 C 42,20 44,22 50,22 C 56,22 60,15 68,13 L 80,11 L 80,36 L 0,36 Z" fill="url(#g-emerald)"/>
                <path d="M 0,30 C 10,28 14,25 18,24 C 24,23 26,22 34,21 C 42,20 44,22 50,22 C 56,22 60,15 68,13 L 80,11" stroke="#10b981" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div class="mt-3">
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $stats['active_orders'] }}</p>
            <p class="text-xs font-medium text-slate-500 mt-1">{{ __('app.active_orders') }}</p>
        </div>
    </a>

    {{-- Active Projects --}}
    <a href="{{ route('enduser.projects.index') }}"
       class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 flex flex-col justify-between overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-violet-50/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-2xl"></div>
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <svg class="w-20 h-10" viewBox="0 0 80 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="g-violet" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M 0,32 C 10,30 14,28 20,26 C 26,24 28,23 34,22 C 40,21 42,20 48,18 C 54,16 56,20 62,20 C 68,20 72,16 80,14 L 80,36 L 0,36 Z" fill="url(#g-violet)"/>
                <path d="M 0,32 C 10,30 14,28 20,26 C 26,24 28,23 34,22 C 40,21 42,20 48,18 C 54,16 56,20 62,20 C 68,20 72,16 80,14" stroke="#8b5cf6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div class="mt-3">
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $stats['active_projects'] }}</p>
            <p class="text-xs font-medium text-slate-500 mt-1">{{ __('app.active_projects') }}</p>
        </div>
    </a>

    {{-- Completed Projects --}}
    <a href="{{ route('enduser.projects.index') }}"
       class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 flex flex-col justify-between overflow-hidden group col-span-2 sm:col-span-1">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-50/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-2xl"></div>
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <svg class="w-20 h-10" viewBox="0 0 80 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="g-teal" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#14b8a6" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M 0,32 C 8,32 12,30 16,29 C 22,28 26,27 30,26 C 36,25 40,24 46,22 C 52,20 56,19 62,18 C 68,17 72,16 80,14 L 80,36 L 0,36 Z" fill="url(#g-teal)"/>
                <path d="M 0,32 C 8,32 12,30 16,29 C 22,28 26,27 30,26 C 36,25 40,24 46,22 C 52,20 56,19 62,18 C 68,17 72,16 80,14" stroke="#14b8a6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div class="mt-3">
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $stats['completed_projects'] }}</p>
            <p class="text-xs font-medium text-slate-500 mt-1">{{ __('app.completed_projects') }}</p>
        </div>
    </a>

</div>

{{-- Track Quotations + Accepted Quotations --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Track Quotations (wide) --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-800">{{ __('app.track_quotations') }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.latest_quotation_req') }}</p>
            </div>
            <a href="{{ route('enduser.quotations.index') }}"
               class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors border border-emerald-100">
                {{ __('app.view_all') }}
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.quotation_id') }}</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.date') }}</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.items') }}</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.status') }}</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentQuotations as $quotation)
                    @php
                        $statusVal = $quotation->status?->value ?? 'pending';
                        $dotColor  = match($statusVal) {
                            'accepted','approved' => 'bg-emerald-500',
                            'submitted'           => 'bg-amber-400',
                            'tender'              => 'bg-orange-400',
                            'in_review'           => 'bg-blue-500',
                            'quoted'              => 'bg-indigo-500',
                            'rejected','cancelled'=> 'bg-red-500',
                            default               => 'bg-slate-300',
                        };
                        $badge = match($statusVal) {
                            'accepted','approved' => ['bg-emerald-50 text-emerald-700 border border-emerald-200', __('app.status_accepted')],
                            'submitted'           => ['bg-amber-50 text-amber-700 border border-amber-200',       __('app.status_submitted')],
                            'tender'              => ['bg-orange-50 text-orange-700 border border-orange-200',     __('app.status_tender')],
                            'in_review'           => ['bg-blue-50 text-blue-700 border border-blue-200',           __('app.status_in_review')],
                            'quoted'              => ['bg-indigo-50 text-indigo-700 border border-indigo-200',     __('app.status_quoted')],
                            'rejected','cancelled'=> ['bg-red-50 text-red-700 border border-red-200',             __('app.status_rejected')],
                            default               => ['bg-slate-100 text-slate-600',                               ucfirst($statusVal)],
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full {{ $dotColor }} flex-shrink-0 shadow-sm"></span>
                                <span class="font-bold text-slate-800 text-sm">#{{ $quotation->id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-slate-500 text-sm whitespace-nowrap">{{ $quotation->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3.5 text-slate-500 text-sm">{{ $quotation->items_count }} {{ __('app.items') }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                               class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap">
                                {{ __('app.view') }} &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-400">{{ __('app.no_quotations_yet') }}</p>
                                <a href="{{ route('enduser.quotations.create') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">{{ __('app.create_one') }} &rarr;</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/60">
            <a href="{{ route('enduser.quotations.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                {{ __('app.view_all_quotations') }} &rarr;
            </a>
        </div>
    </div>

    {{-- Accepted Quotations (narrow) --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-800">{{ __('app.accepted_quotations') }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.ready_for_order') }}</p>
            </div>
            @if($stats['accepted_quotations'] > 0)
            <span class="min-w-[1.75rem] h-7 px-2 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center shadow-sm">
                {{ $stats['accepted_quotations'] }}
            </span>
            @endif
        </div>
        <div class="flex-1 divide-y divide-slate-50">
            @forelse($acceptedQuotations as $quotation)
            <div class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800">#{{ $quotation->id }}</p>
                        <p class="text-xs text-slate-400">{{ $quotation->created_at->format('M d, Y') }} &middot; {{ $quotation->items_count }} {{ __('app.items') }}</p>
                    </div>
                </div>
                <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                   class="flex-shrink-0 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-xl transition-colors shadow-sm whitespace-nowrap">
                    {{ __('app.order') }}
                </a>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400">{{ __('app.no_accepted_quotations') }}</p>
            </div>
            @endforelse
        </div>
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/60 mt-auto">
            <a href="{{ route('enduser.quotations.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                {{ __('app.view_all_accepted') }} &rarr;
            </a>
        </div>
    </div>

</div>

{{-- Recent Orders --}}
<div class="mt-5 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-sm font-bold text-slate-800">{{ __('app.recent_orders') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('app.your_latest_orders') }}</p>
        </div>
        <a href="{{ route('enduser.orders.index') }}"
           class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors border border-emerald-100">
            {{ __('app.view_all') }}
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.order_no') }}</th>
                    <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.date') }}</th>
                    <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.total') }}</th>
                    <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.status') }}</th>
                    <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($recentOrders as $order)
                @php
                    $orderStatus = $order->status?->value ?? 'open';
                    $orderBadge  = match($orderStatus) {
                        'open'   => ['bg-emerald-50 text-emerald-700 border border-emerald-200', __('app.status_open')],
                        'closed' => ['bg-slate-100 text-slate-600 border border-slate-200',       __('app.status_closed')],
                        default  => ['bg-slate-100 text-slate-600 border border-slate-200',       ucfirst($orderStatus)],
                    };
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <span class="font-bold text-slate-800 text-sm">{{ $order->order_no }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-3.5 text-slate-500 text-sm whitespace-nowrap">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-3.5 font-bold text-slate-800 text-sm whitespace-nowrap">
                        {{ $order->currency ?? 'SAR' }} {{ number_format($order->grand_total, 2) }}
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $orderBadge[0] }}">
                            {{ $orderBadge[1] }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        <a href="{{ route('enduser.orders.show', $order->uuid) }}"
                           class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap">
                            {{ __('app.view') }} &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-slate-400">{{ __('app.no_orders_yet') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
