@extends('layouts.enduser-app')

@section('title', __('app.dashboard') . ' – Qimta')
@section('page-title', __('app.dashboard'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.dashboard') }}</span>
@endsection

@php $isRtl = app()->getLocale() === 'ar'; @endphp

@section('content')

{{-- Welcome Banner --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-{{ $isRtl ? 'l' : 'r' }} from-emerald-600 via-emerald-500 to-teal-500 p-6 sm:p-8 mb-8 shadow-lg shadow-emerald-500/20">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 400 200" fill="none"><circle cx="350" cy="30" r="120" fill="white"/><circle cx="50" cy="180" r="80" fill="white"/></svg>
    </div>
    <div class="relative z-10">
        <h1 class="text-xl sm:text-2xl font-bold text-white mb-1">
            {{ __('app.welcome_back') }}{{ auth()->user()->name ? ', ' . auth()->user()->name : '' }}! 👋
        </h1>
        <p class="text-emerald-100 text-sm sm:text-base">
            {{ __('app.welcome_desc') }}
        </p>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5 mb-8">
    {{-- Total Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}"
       class="group relative bg-white rounded-2xl p-5 shadow-sm border border-slate-100
              hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-0.5
              transition-all duration-300 overflow-hidden">
        <div class="absolute top-0 {{ $isRtl ? 'left-0' : 'right-0' }} w-20 h-20 bg-gradient-to-br from-blue-500/5 to-blue-500/10 rounded-full -translate-y-6 {{ $isRtl ? '-translate-x-6' : 'translate-x-6' }}"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-blue-500/25 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_quotations'] }}</p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">{{ __('app.total_quotations') }}</p>
        </div>
    </a>

    {{-- Active Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}"
       class="group relative bg-white rounded-2xl p-5 shadow-sm border border-slate-100
              hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-0.5
              transition-all duration-300 overflow-hidden">
        <div class="absolute top-0 {{ $isRtl ? 'left-0' : 'right-0' }} w-20 h-20 bg-gradient-to-br from-amber-500/5 to-amber-500/10 rounded-full -translate-y-6 {{ $isRtl ? '-translate-x-6' : 'translate-x-6' }}"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['active_quotations'] }}</p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">{{ __('app.active_quotations') }}</p>
        </div>
    </a>

    {{-- Active Orders --}}
    <a href="{{ route('enduser.orders.index') }}"
       class="group relative bg-white rounded-2xl p-5 shadow-sm border border-slate-100
              hover:shadow-lg hover:shadow-emerald-500/10 hover:-translate-y-0.5
              transition-all duration-300 overflow-hidden">
        <div class="absolute top-0 {{ $isRtl ? 'left-0' : 'right-0' }} w-20 h-20 bg-gradient-to-br from-emerald-500/5 to-emerald-500/10 rounded-full -translate-y-6 {{ $isRtl ? '-translate-x-6' : 'translate-x-6' }}"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/25 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['active_orders'] }}</p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">{{ __('app.active_orders') }}</p>
        </div>
    </a>

    {{-- Active Projects --}}
    <a href="{{ route('enduser.projects.index') }}"
       class="group relative bg-white rounded-2xl p-5 shadow-sm border border-slate-100
              hover:shadow-lg hover:shadow-violet-500/10 hover:-translate-y-0.5
              transition-all duration-300 overflow-hidden">
        <div class="absolute top-0 {{ $isRtl ? 'left-0' : 'right-0' }} w-20 h-20 bg-gradient-to-br from-violet-500/5 to-violet-500/10 rounded-full -translate-y-6 {{ $isRtl ? '-translate-x-6' : 'translate-x-6' }}"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-violet-500/25 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['active_projects'] }}</p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">{{ __('app.active_projects') }}</p>
        </div>
    </a>

    {{-- Completed Projects --}}
    <a href="{{ route('enduser.projects.index') }}"
       class="group relative bg-white rounded-2xl p-5 shadow-sm border border-slate-100
              hover:shadow-lg hover:shadow-teal-500/10 hover:-translate-y-0.5
              transition-all duration-300 overflow-hidden col-span-2 sm:col-span-1">
        <div class="absolute top-0 {{ $isRtl ? 'left-0' : 'right-0' }} w-20 h-20 bg-gradient-to-br from-teal-500/5 to-teal-500/10 rounded-full -translate-y-6 {{ $isRtl ? '-translate-x-6' : 'translate-x-6' }}"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-teal-500/25 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['completed_projects'] }}</p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">{{ __('app.completed_projects') }}</p>
        </div>
    </a>
</div>

{{-- Track Quotations + Accepted --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Track Quotations --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-gradient-to-{{ $isRtl ? 'l' : 'r' }} from-white to-slate-50/80">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('app.track_quotations') }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('app.latest_quotation_req') }}</p>
                </div>
            </div>
            <a href="{{ route('enduser.quotations.index') }}"
               class="text-xs font-semibold text-emerald-600 hover:text-white bg-emerald-50 hover:bg-emerald-500
                      px-4 py-2 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-emerald-500/25">
                {{ __('app.view_all') }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 text-{{ $isRtl ? 'right' : 'left' }}">
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.quotation_id') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.date') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.items') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.status') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80">
                    @forelse($recentQuotations as $quotation)
                    <tr class="hover:bg-emerald-50/40 transition-colors duration-150 group">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                #{{ $quotation->id }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">{{ $quotation->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="text-slate-600 font-medium text-xs">{{ __('app.items_count', ['count' => $quotation->items_count]) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $status = $quotation->status?->value ?? 'pending';
                                $badge = match($status) {
                                    'accepted', 'approved' => ['bg-emerald-100 text-emerald-700 ring-emerald-600/20', __('app.status_accepted')],
                                    'submitted'            => ['bg-yellow-100 text-yellow-700 ring-yellow-600/20',   __('app.status_submitted')],
                                    'tender'               => ['bg-orange-100 text-orange-700 ring-orange-600/20',   __('app.status_tender')],
                                    'pending'              => ['bg-amber-100 text-amber-700 ring-amber-600/20',      __('app.status_pending')],
                                    'rejected', 'cancelled'=> ['bg-red-100 text-red-700 ring-red-600/20',            __('app.status_rejected')],
                                    'in_review'            => ['bg-blue-100 text-blue-700 ring-blue-600/20',         __('app.status_in_review')],
                                    'quoted'               => ['bg-indigo-100 text-indigo-700 ring-indigo-600/20',   __('app.status_quoted')],
                                    'draft'                => ['bg-slate-100 text-slate-600 ring-slate-600/20',       __('app.status_draft')],
                                    default                => ['bg-slate-100 text-slate-600 ring-slate-600/20',       ucfirst($status)],
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ring-1 {{ $badge[0] }}">{{ $badge[1] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-all duration-200">
                                {{ __('app.view') }}
                                <svg class="w-3.5 h-3.5 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm text-slate-400">{{ __('app.no_quotations_yet') }}</p>
                                <a href="{{ route('enduser.quotations.create') }}"
                                   class="text-xs font-semibold text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-xl shadow-md shadow-emerald-500/25 transition-all duration-200">
                                    {{ __('app.create_one') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Accepted Quotations --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-{{ $isRtl ? 'l' : 'r' }} from-emerald-500 to-teal-500 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10"><svg viewBox="0 0 200 200" fill="none"><circle cx="160" cy="20" r="80" fill="white"/></svg></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white">{{ __('app.accepted_quotations') }}</h2>
                    <p class="text-xs text-emerald-100 mt-0.5">{{ __('app.ready_for_order') }}</p>
                </div>
                @if($stats['accepted_quotations'] > 0)
                <span class="text-sm font-bold bg-white/20 backdrop-blur-sm text-white w-8 h-8 rounded-xl flex items-center justify-center ring-2 ring-white/30">
                    {{ $stats['accepted_quotations'] }}
                </span>
                @endif
            </div>
        </div>

        <div class="divide-y divide-slate-100/80 flex-1">
            @forelse($acceptedQuotations as $quotation)
            <div class="px-6 py-4 hover:bg-emerald-50/40 transition-colors duration-150 group">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 truncate">#{{ $quotation->id }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $quotation->created_at->format('M d, Y') }} · {{ __('app.items_count', ['count' => $quotation->items_count]) }}</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                       class="text-xs font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600
                              px-4 py-2 rounded-xl transition-all duration-200 shrink-0 shadow-md shadow-emerald-500/25 hover:shadow-lg">
                        {{ __('app.order') }}
                    </a>
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center flex-1 flex flex-col items-center justify-center">
                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-slate-400">{{ __('app.no_accepted_quotations') }}</p>
            </div>
            @endforelse
        </div>

        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50">
            <a href="{{ route('enduser.quotations.index') }}"
               class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                {{ __('app.view_all_accepted') }}
                <svg class="w-3.5 h-3.5 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>

{{-- Recent Orders --}}
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-gradient-to-{{ $isRtl ? 'l' : 'r' }} from-white to-slate-50/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ __('app.recent_orders') }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.your_latest_orders') }}</p>
            </div>
        </div>
        <a href="{{ route('enduser.orders.index') }}"
           class="text-xs font-semibold text-emerald-600 hover:text-white bg-emerald-50 hover:bg-emerald-500
                  px-4 py-2 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-emerald-500/25">
            {{ __('app.view_all') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-{{ $isRtl ? 'right' : 'left' }}">
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.order_no') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.date') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.total') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.status') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-emerald-50/40 transition-colors duration-150 group">
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            {{ $order->order_no }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-900">{{ number_format($order->grand_total, 2) }}</span>
                        <span class="text-xs text-slate-400 {{ $isRtl ? 'mr-0.5' : 'ml-0.5' }}">{{ $order->currency ?? 'SAR' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $orderStatus = $order->status?->value ?? 'pending';
                            $orderBadge = match($orderStatus) {
                                'pending'    => ['bg-yellow-100 text-yellow-700 ring-yellow-600/20',  __('app.status_pending')],
                                'confirmed'  => ['bg-blue-100 text-blue-700 ring-blue-600/20',        __('app.status_confirmed')],
                                'processing' => ['bg-indigo-100 text-indigo-700 ring-indigo-600/20',  __('app.status_processing')],
                                'shipped'    => ['bg-cyan-100 text-cyan-700 ring-cyan-600/20',        __('app.status_shipped')],
                                'delivered'  => ['bg-emerald-100 text-emerald-700 ring-emerald-600/20',__('app.status_delivered')],
                                'completed'  => ['bg-green-100 text-green-700 ring-green-600/20',     __('app.status_completed')],
                                'cancelled'  => ['bg-red-100 text-red-700 ring-red-600/20',           __('app.status_cancelled')],
                                'refunded'   => ['bg-red-100 text-red-700 ring-red-600/20',           __('app.status_refunded')],
                                default      => ['bg-slate-100 text-slate-600 ring-slate-600/20',     ucfirst($orderStatus)],
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ring-1 {{ $orderBadge[0] }}">{{ $orderBadge[1] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('enduser.orders.show', $order->uuid) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-all duration-200">
                            {{ __('app.view') }}
                            <svg class="w-3.5 h-3.5 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
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

{{-- Active Projects --}}
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-gradient-to-{{ $isRtl ? 'l' : 'r' }} from-white to-slate-50/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md shadow-violet-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ __('app.active_projects_section') }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.ongoing_projects') }}</p>
            </div>
        </div>
        <a href="{{ route('enduser.projects.index') }}"
           class="text-xs font-semibold text-emerald-600 hover:text-white bg-emerald-50 hover:bg-emerald-500
                  px-4 py-2 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-emerald-500/25">
            {{ __('app.view_all') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-{{ $isRtl ? 'right' : 'left' }}">
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.project') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.start_date') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.status') }}</th>
                    <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('app.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80">
                @forelse($activeProjects as $project)
                <tr class="hover:bg-violet-50/40 transition-colors duration-150 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-violet-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $project->name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">#{{ $project->project_no }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-xs">{{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $projStatus = $project->status?->value ?? 'pending';
                            $projBadge = match($projStatus) {
                                'active'    => ['bg-blue-100 text-blue-700 ring-blue-600/20',     __('app.status_active')],
                                'pending'   => ['bg-amber-100 text-amber-700 ring-amber-600/20',  __('app.status_pending')],
                                'on_hold'   => ['bg-orange-100 text-orange-700 ring-orange-600/20',__('app.status_on_hold')],
                                'completed' => ['bg-green-100 text-green-700 ring-green-600/20',  __('app.status_completed')],
                                'cancelled' => ['bg-red-100 text-red-700 ring-red-600/20',        __('app.status_cancelled')],
                                default     => ['bg-slate-100 text-slate-600 ring-slate-600/20',   ucfirst($projStatus)],
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ring-1 {{ $projBadge[0] }}">{{ $projBadge[1] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('enduser.projects.show', $project->uuid) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-all duration-200">
                            {{ __('app.view') }}
                            <svg class="w-3.5 h-3.5 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <p class="text-sm text-slate-400">{{ __('app.no_active_projects') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
