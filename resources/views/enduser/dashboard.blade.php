@extends('layouts.enduser-app')

@section('title', __('app.dashboard') . ' â€“ Qimta')
@section('page-title', __('app.dashboard'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.dashboard') }}</span>
@endsection

@section('content')

{{-- â”€â”€ Stat Cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-7">

    {{-- Total Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}"
       class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <svg class="w-16 h-8 opacity-60" viewBox="0 0 64 32" fill="none">
                <polyline points="0,28 10,22 22,18 34,12 46,16 56,8 64,4" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-bold text-slate-900">{{ $stats['total_quotations'] }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.total_quotations') }}</p>
        </div>
    </a>

    {{-- Active Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}"
       class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
            </div>
            <svg class="w-16 h-8 opacity-60" viewBox="0 0 64 32" fill="none">
                <polyline points="0,24 12,20 24,24 36,14 48,18 60,10 64,12" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-bold text-slate-900">{{ $stats['active_quotations'] }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.active_quotations') }}</p>
        </div>
    </a>

    {{-- Active Orders --}}
    <a href="{{ route('enduser.orders.index') }}"
       class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <svg class="w-16 h-8 opacity-60" viewBox="0 0 64 32" fill="none">
                <polyline points="0,28 14,24 28,20 40,22 52,14 64,10" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-bold text-slate-900">{{ $stats['active_orders'] }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.active_orders') }}</p>
        </div>
    </a>

    {{-- Active Projects --}}
    <a href="{{ route('enduser.projects.index') }}"
       class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <svg class="w-16 h-8 opacity-60" viewBox="0 0 64 32" fill="none">
                <polyline points="0,30 16,26 28,22 40,18 52,20 64,14" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-bold text-slate-900">{{ $stats['active_projects'] }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.active_projects') }}</p>
        </div>
    </a>

    {{-- Completed Projects --}}
    <a href="{{ route('enduser.projects.index') }}"
       class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between col-span-2 sm:col-span-1">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <svg class="w-16 h-8 opacity-60" viewBox="0 0 64 32" fill="none">
                <polyline points="0,30 12,28 24,26 36,22 50,18 64,14" stroke="#14b8a6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="mt-4">
            <p class="text-3xl font-bold text-slate-900">{{ $stats['completed_projects'] }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ __('app.completed_projects') }}</p>
        </div>
    </a>

</div>

{{-- â”€â”€ Track Quotations + Accepted Quotations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Track Quotations (wide) --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.track_quotations') }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.latest_quotation_req') }}</p>
            </div>
            <a href="{{ route('enduser.quotations.index') }}"
               class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                {{ __('app.view_all') }}
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.quotation_id') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.date') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.items') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.status') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
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
                            'accepted','approved' => ['bg-emerald-50 text-emerald-700 border border-emerald-100', __('app.status_accepted')],
                            'submitted'           => ['bg-amber-50 text-amber-700 border border-amber-100',   __('app.status_submitted')],
                            'tender'              => ['bg-orange-50 text-orange-700 border border-orange-100', __('app.status_tender')],
                            'in_review'           => ['bg-blue-50 text-blue-700 border border-blue-100',       __('app.status_in_review')],
                            'quoted'              => ['bg-indigo-50 text-indigo-700 border border-indigo-100', __('app.status_quoted')],
                            'rejected','cancelled'=> ['bg-red-50 text-red-700 border border-red-100',         __('app.status_rejected')],
                            default               => ['bg-slate-100 text-slate-600',                            ucfirst($statusVal)],
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                                <span class="font-semibold text-slate-900">#{{ $quotation->id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $quotation->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $quotation->items_count }} {{ __('app.items') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                               class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap">
                                {{ __('app.view') }} â†’
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400">
                            {{ __('app.no_quotations_yet') }}
                            <a href="{{ route('enduser.quotations.create') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold ml-1">{{ __('app.create_one') }} â†’</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
            <a href="{{ route('enduser.quotations.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                {{ __('app.view_all_quotations') }} â†’
            </a>
        </div>
    </div>

    {{-- Accepted Quotations (narrow) --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.accepted_quotations') }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.ready_for_order') }}</p>
            </div>
            @if($stats['accepted_quotations'] > 0)
            <span class="w-7 h-7 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center">
                {{ $stats['accepted_quotations'] }}
            </span>
            @endif
        </div>
        <div class="flex-1 divide-y divide-slate-100">
            @forelse($acceptedQuotations as $quotation)
            <div class="px-6 py-4 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900">#{{ $quotation->id }}</p>
                            <p class="text-xs text-slate-400">{{ $quotation->created_at->format('M d, Y') }} Â· {{ $quotation->items_count }} {{ __('app.items') }}</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                   class="flex-shrink-0 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-xl transition-colors shadow-sm">
                    {{ __('app.order') }}
                </a>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-sm text-slate-400">
                {{ __('app.no_accepted_quotations') }}
            </div>
            @endforelse
        </div>
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50 mt-auto">
            <a href="{{ route('enduser.quotations.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                {{ __('app.view_all_accepted') }} â†’
            </a>
        </div>
    </div>

</div>

{{-- â”€â”€ Recent Orders â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="mt-6 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-semibold text-slate-900">{{ __('app.recent_orders') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('app.your_latest_orders') }}</p>
        </div>
        <a href="{{ route('enduser.orders.index') }}"
           class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
            {{ __('app.view_all') }}
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-left">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.order_no') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.date') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.total') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.status') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('app.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentOrders as $order)
                @php
                    $orderStatus = $order->status?->value ?? 'open';
                    $orderBadge  = match($orderStatus) {
                        'open'   => ['bg-emerald-50 text-emerald-700 border border-emerald-100', __('app.status_open')],
                        'closed' => ['bg-slate-100 text-slate-600',                              __('app.status_closed')],
                        default  => ['bg-slate-100 text-slate-600',                              ucfirst($orderStatus)],
                    };
                @endphp
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-900">{{ $order->order_no }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">
                        {{ $order->currency ?? 'SAR' }} {{ number_format($order->grand_total, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $orderBadge[0] }}">
                            {{ $orderBadge[1] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('enduser.orders.show', $order->uuid) }}"
                           class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap">
                            {{ __('app.view') }} â†’
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400">
                        {{ __('app.no_orders_yet') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
