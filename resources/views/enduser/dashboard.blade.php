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

{{-- BOQ Promo Banner --}}
<div x-data="{ show: !localStorage.getItem('boq_banner_dismissed') }"
     x-show="show"
     x-cloak
     class="relative mb-6 rounded-2xl overflow-hidden"
     style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 45%, #c4b5fd 75%, #a78bfa 100%); min-height: 160px;">

    {{-- Decorative sparkles (positioned safely in the left text zone) --}}
    <span class="absolute top-5 left-64 text-pink-400 select-none pointer-events-none" style="font-size:16px; z-index:1;">&#10022;</span>
    <span class="absolute bottom-8 left-48 text-teal-400 select-none pointer-events-none" style="font-size:10px; z-index:1;">&#10022;</span>
    <span class="absolute top-10 left-80 text-pink-300 select-none pointer-events-none" style="font-size:10px; z-index:1;">+</span>
    <span class="absolute bottom-5 left-72 text-violet-300 select-none pointer-events-none" style="font-size:10px; z-index:1;">&#9670;</span>

    {{-- Close button --}}
    <button @click="show = false; localStorage.setItem('boq_banner_dismissed', '1')"
            class="absolute top-4 end-4 w-7 h-7 rounded-full bg-white/30 hover:bg-white/50 flex items-center justify-center transition-colors"
            style="z-index:20;">
        <svg class="w-3.5 h-3.5 text-violet-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Inner flex row: [text] [cta] [illustration] --}}
    <div class="flex items-center h-full" style="min-height:160px;">

        {{-- Text block --}}
        <div class="flex-1 px-8 py-7 min-w-0" style="z-index:2; position:relative;">
            <h2 class="text-2xl sm:text-3xl font-black text-violet-900 leading-tight mb-2">
                {{ __('app.banner_title') }}
            </h2>
            <p class="text-sm text-violet-700 font-medium mb-1">{{ __('app.banner_subtitle') }}</p>
            <p class="text-xs text-violet-500 font-semibold tracking-wide">{{ __('app.banner_tagline') }}</p>
        </div>

        {{-- CTA block --}}
        <div class="flex flex-col items-center gap-3 px-6 py-7 flex-shrink-0" style="z-index:2; position:relative;">
            <span class="inline-flex items-center gap-1 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ __('app.banner_free_badge') }}
            </span>
            <a href="{{ route('enduser.boqs.create') }}"
               class="inline-flex items-center gap-2 bg-violet-900 hover:bg-violet-800 text-white text-sm font-bold px-6 py-3 rounded-xl shadow-lg transition-all whitespace-nowrap">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.banner_cta') }}
            </a>
        </div>

        {{-- Illustration --}}
        <div class="hidden lg:block flex-shrink-0 self-end" style="width:220px; z-index:2; position:relative;">
            <svg viewBox="0 0 220 165" xmlns="http://www.w3.org/2000/svg" style="display:block; width:220px; height:165px;">
                {{-- sparkles in SVG --}}
                <text x="8"   y="22"  font-size="15" fill="#f472b6">&#10022;</text>
                <text x="196" y="26"  font-size="11" fill="#2dd4bf">&#10022;</text>
                <text x="200" y="150" font-size="9"  fill="#a78bfa">&#9670;</text>
                {{-- Calculator body (back) --}}
                <rect x="18" y="38" width="80" height="118" rx="10" fill="#7c3aed" opacity="0.88"/>
                <rect x="26" y="48" width="64" height="26" rx="5" fill="#a78bfa"/>
                <rect x="30" y="53" width="56" height="16" rx="3" fill="#ede9fe"/>
                {{-- Calc buttons --}}
                <rect x="28" y="82"  width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="46" y="82"  width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="64" y="82"  width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="82" y="82"  width="14" height="10" rx="2" fill="#8b5cf6"/>
                <rect x="28" y="96"  width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="46" y="96"  width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="64" y="96"  width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="82" y="96"  width="14" height="24" rx="2" fill="#8b5cf6"/>
                <rect x="28" y="110" width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="46" y="110" width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="64" y="110" width="14" height="10" rx="2" fill="#c4b5fd"/>
                <rect x="28" y="124" width="32" height="10" rx="2" fill="#6d28d9"/>
                <rect x="64" y="124" width="14" height="10" rx="2" fill="#c4b5fd"/>
                {{-- Clipboard (front, overlapping calculator) --}}
                <rect x="72" y="12" width="110" height="140" rx="10" fill="white" opacity="0.97"/>
                <rect x="109" y="6"  width="36" height="16" rx="5" fill="#7c3aed"/>
                <rect x="115" y="2"  width="24" height="12" rx="4" fill="#5b21b6"/>
                {{-- Lines on clipboard --}}
                <rect x="84" y="36" width="86" height="6" rx="3" fill="#e2e8f0"/>
                <rect x="84" y="48" width="86" height="6" rx="3" fill="#e2e8f0"/>
                <rect x="84" y="60" width="68" height="6" rx="3" fill="#e2e8f0"/>
                <rect x="84" y="72" width="86" height="6" rx="3" fill="#e2e8f0"/>
                <rect x="84" y="84" width="54" height="6" rx="3" fill="#e2e8f0"/>
                <rect x="84" y="96" width="86" height="6" rx="3" fill="#e2e8f0"/>
                <rect x="84" y="108" width="44" height="6" rx="3" fill="#e2e8f0"/>
                {{-- FREE badge --}}
                <rect x="112" y="124" width="50" height="22" rx="7" fill="#22c55e"/>
                <text x="137" y="139" text-anchor="middle" font-size="9" font-weight="900" fill="white" font-family="Arial,sans-serif">FREE</text>
            </svg>
        </div>

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
