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

{{-- Stats + BOQ Promo --}}
<div class="grid grid-cols-1 xl:grid-cols-4 gap-4 mb-7">

{{-- Left: Stat Cards --}}
<div class="xl:col-span-3 grid grid-cols-2 sm:grid-cols-3 gap-4 content-start">

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
       class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 flex flex-col justify-between overflow-hidden group">
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

{{-- Right: BOQ Promo Card --}}
<a href="{{ route('enduser.boqs.create') }}"
   class="group relative xl:col-span-1 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col p-6">

    {{-- Radial blue blob (bottom-right) --}}
    <div class="absolute pointer-events-none"
         style="width:260px;height:260px;bottom:-80px;right:-60px;
                background:radial-gradient(circle,rgba(99,102,241,0.13) 0%,rgba(147,197,253,0.10) 40%,transparent 70%);
                border-radius:50%;"></div>

    {{-- Badge --}}
    <div class="inline-flex items-center gap-1.5 self-start bg-slate-100 text-slate-500 text-xs font-semibold px-2.5 py-1 rounded-full mb-4">
        <svg class="w-3 h-3 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        New
    </div>

    {{-- Headline --}}
    <h3 class="text-2xl font-black text-slate-900 leading-snug mb-2">Create BOQ<br>for Free</h3>

    {{-- Subtitle --}}
    <p class="text-slate-400 text-sm leading-relaxed mb-5">
        Generate accurate BOQs in minutes and streamline your construction estimation process.
    </p>

    {{-- CTA Button --}}
    <span class="inline-flex items-center gap-2 self-start bg-indigo-600 group-hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md transition-colors duration-200">
        {{ __('app.banner_cta') }}
        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
        </svg>
    </span>

    {{-- BOQ Illustration --}}
    <div class="relative mt-auto pt-4 flex items-end justify-end">
        <svg viewBox="0 0 200 130" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:220px;height:auto;">
            {{-- Clipboard shadow / back --}}
            <rect x="62" y="18" width="104" height="100" rx="8" fill="#e0e7ff" opacity="0.6"/>
            {{-- Clipboard body --}}
            <rect x="56" y="12" width="104" height="100" rx="8" fill="white" stroke="#e2e8f0" stroke-width="1"/>
            {{-- Clip --}}
            <rect x="96" y="6" width="24" height="14" rx="4" fill="#c7d2fe"/>
            <rect x="100" y="4" width="16" height="10" rx="3" fill="#a5b4fc"/>
            {{-- BOQ label --}}
            <text x="108" y="36" text-anchor="middle" font-size="11" font-weight="800" fill="#312e81" font-family="Arial,sans-serif">BOQ</text>
            {{-- Lines --}}
            <rect x="68" y="45" width="80" height="5" rx="2.5" fill="#e2e8f0"/>
            <rect x="68" y="55" width="80" height="5" rx="2.5" fill="#e2e8f0"/>
            <rect x="68" y="65" width="60" height="5" rx="2.5" fill="#e2e8f0"/>
            <rect x="68" y="75" width="80" height="5" rx="2.5" fill="#e2e8f0"/>
            <rect x="68" y="85" width="44" height="5" rx="2.5" fill="#e2e8f0"/>
            {{-- Calculator --}}
            <rect x="24" y="52" width="62" height="70" rx="8" fill="#cbd5e1"/>
            <rect x="28" y="56" width="54" height="62" rx="6" fill="#f1f5f9"/>
            {{-- Calc screen --}}
            <rect x="33" y="61" width="44" height="14" rx="3" fill="#bfdbfe"/>
            {{-- Calc buttons row 1 --}}
            <rect x="33" y="80" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="45" y="80" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="57" y="80" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="69" y="80" width="8" height="7" rx="2" fill="#6366f1"/>
            {{-- Calc buttons row 2 --}}
            <rect x="33" y="91" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="45" y="91" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="57" y="91" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="69" y="91" width="8" height="7" rx="2" fill="#6366f1"/>
            {{-- Calc buttons row 3 --}}
            <rect x="33" y="102" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="45" y="102" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="57" y="102" width="9" height="7" rx="2" fill="#94a3b8"/>
            <rect x="69" y="102" width="8" height="15" rx="2" fill="#6366f1"/>
            {{-- Checkmark badge --}}
            <circle cx="154" cy="108" r="16" fill="#3b82f6"/>
            <polyline points="146,108 152,114 163,100" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
    </div>

</a>

</div>{{-- /outer grid --}}

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
