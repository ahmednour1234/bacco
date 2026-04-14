@extends('layouts.enduser-app')

@section('title', __('app.reports') . ' – Qimta')
@section('page-title', __('app.reports'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.reports') }}</span>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════
     FINANCIAL SUMMARY CARDS
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

    {{-- Total Order Value --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.total_orders_value') }}</p>
        </div>
        <p class="text-2xl font-bold text-slate-900">SAR {{ number_format($financials['total_order_value'], 2) }}</p>
    </div>

    {{-- Total Paid --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.total_paid') }}</p>
        </div>
        <p class="text-2xl font-bold text-emerald-600">SAR {{ number_format($financials['total_paid'], 2) }}</p>
    </div>

    {{-- Pending Payments --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.pending_payments') }}</p>
        </div>
        <p class="text-2xl font-bold text-amber-600">SAR {{ number_format($financials['pending_payments'], 2) }}</p>
    </div>

    {{-- Outstanding Balance --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.outstanding') }}</p>
        </div>
        <p class="text-2xl font-bold text-red-600">SAR {{ number_format($financials['outstanding'], 2) }}</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     STATUS BREAKDOWNS: Quotations + Orders + Projects
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Quotations by Status --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">{{ __('app.quotations_by_status') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('app.breakdown_quotations') }}</p>
        </div>
        <div class="px-6 py-4 space-y-3">
            @php
                $quotationStatuses = [
                    'draft'      => ['label' => __('app.status_draft'),      'color' => 'bg-slate-400'],
                    'submitted'  => ['label' => __('app.status_submitted'),  'color' => 'bg-yellow-400'],
                    'tender'     => ['label' => __('app.status_tender'),     'color' => 'bg-orange-400'],
                    'in_review'  => ['label' => __('app.status_in_review'),  'color' => 'bg-blue-400'],
                    'quoted'     => ['label' => __('app.status_quoted'),     'color' => 'bg-indigo-400'],
                    'accepted'   => ['label' => __('app.status_accepted'),   'color' => 'bg-emerald-400'],
                    'rejected'   => ['label' => __('app.status_rejected'),   'color' => 'bg-red-400'],
                    'cancelled'  => ['label' => __('app.status_cancelled'),  'color' => 'bg-red-300'],
                ];
                $totalQuotations = array_sum($quotationsByStatus);
            @endphp
            @if($totalQuotations > 0)
                @foreach($quotationStatuses as $key => $meta)
                    @php $count = $quotationsByStatus[$key] ?? 0; @endphp
                    @if($count > 0)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-slate-600">{{ $meta['label'] }}</span>
                            <span class="font-semibold text-slate-900">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="{{ $meta['color'] }} h-2 rounded-full transition-all" style="width: {{ round(($count / $totalQuotations) * 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                @endforeach
                <div class="pt-2 border-t border-slate-100 flex justify-between text-sm">
                    <span class="font-medium text-slate-500">{{ __('app.total') }}</span>
                    <span class="font-bold text-slate-900">{{ $totalQuotations }}</span>
                </div>
            @else
                <div class="py-6 text-center text-sm text-slate-400">{{ __('app.no_quotations') }}</div>
            @endif
        </div>
    </div>

    {{-- Orders by Status --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">{{ __('app.orders_by_status') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('app.breakdown_orders') }}</p>
        </div>
        <div class="px-6 py-4 space-y-3">
            @php
                $orderStatuses = [
                    'pending'    => ['label' => __('app.status_pending'),    'color' => 'bg-yellow-400'],
                    'confirmed'  => ['label' => __('app.status_confirmed'),  'color' => 'bg-blue-400'],
                    'processing' => ['label' => __('app.status_processing'), 'color' => 'bg-indigo-400'],
                    'shipped'    => ['label' => __('app.status_shipped'),    'color' => 'bg-cyan-400'],
                    'delivered'  => ['label' => __('app.status_delivered'),  'color' => 'bg-emerald-400'],
                    'completed'  => ['label' => __('app.status_completed'),  'color' => 'bg-green-400'],
                    'cancelled'  => ['label' => __('app.status_cancelled'),  'color' => 'bg-red-400'],
                    'refunded'   => ['label' => __('app.status_refunded'),   'color' => 'bg-red-300'],
                ];
                $totalOrders = array_sum($ordersByStatus);
            @endphp
            @if($totalOrders > 0)
                @foreach($orderStatuses as $key => $meta)
                    @php $count = $ordersByStatus[$key] ?? 0; @endphp
                    @if($count > 0)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-slate-600">{{ $meta['label'] }}</span>
                            <span class="font-semibold text-slate-900">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="{{ $meta['color'] }} h-2 rounded-full transition-all" style="width: {{ round(($count / $totalOrders) * 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                @endforeach
                <div class="pt-2 border-t border-slate-100 flex justify-between text-sm">
                    <span class="font-medium text-slate-500">{{ __('app.total') }}</span>
                    <span class="font-bold text-slate-900">{{ $totalOrders }}</span>
                </div>
            @else
                <div class="py-6 text-center text-sm text-slate-400">{{ __('app.no_orders') }}</div>
            @endif
        </div>
    </div>

    {{-- Projects by Status --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">{{ __('app.projects_by_status') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('app.breakdown_projects') }}</p>
        </div>
        <div class="px-6 py-4 space-y-3">
            @php
                $projectStatuses = [
                    'pending'   => ['label' => __('app.status_pending'),   'color' => 'bg-amber-400'],
                    'active'    => ['label' => __('app.status_active'),    'color' => 'bg-blue-400'],
                    'on_hold'   => ['label' => __('app.status_on_hold'),   'color' => 'bg-orange-400'],
                    'completed' => ['label' => __('app.status_completed'), 'color' => 'bg-green-400'],
                    'cancelled' => ['label' => __('app.status_cancelled'), 'color' => 'bg-red-400'],
                ];
                $totalProjects = array_sum($projectsByStatus);
            @endphp
            @if($totalProjects > 0)
                @foreach($projectStatuses as $key => $meta)
                    @php $count = $projectsByStatus[$key] ?? 0; @endphp
                    @if($count > 0)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-slate-600">{{ $meta['label'] }}</span>
                            <span class="font-semibold text-slate-900">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="{{ $meta['color'] }} h-2 rounded-full transition-all" style="width: {{ round(($count / $totalProjects) * 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                @endforeach
                <div class="pt-2 border-t border-slate-100 flex justify-between text-sm">
                    <span class="font-medium text-slate-500">{{ __('app.total') }}</span>
                    <span class="font-bold text-slate-900">{{ $totalProjects }}</span>
                </div>
            @else
                <div class="py-6 text-center text-sm text-slate-400">{{ __('app.no_projects') }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MONTHLY ORDER TREND
══════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-semibold text-slate-900">{{ __('app.monthly_orders') }}</h2>
        <p class="text-xs text-slate-400 mt-0.5">{{ __('app.monthly_orders_desc') }}</p>
    </div>
    @if($monthlyOrders->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.month') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.orders') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.total') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.trend') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php $maxTotal = $monthlyOrders->max('total') ?: 1; @endphp
                @foreach($monthlyOrders as $month)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-900">
                        {{ \Carbon\Carbon::parse($month->month . '-01')->format('M Y') }}
                    </td>
                    <td class="px-6 py-4 text-slate-700">{{ $month->count }}</td>
                    <td class="px-6 py-4 font-medium text-slate-900">SAR {{ number_format($month->total, 2) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-100 rounded-full h-2 min-w-[100px]">
                                <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ round(($month->total / $maxTotal) * 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-6 py-8 text-center text-sm text-slate-400">{{ __('app.no_orders_6_months') }}</div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     RECENT PAYMENTS
══════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-semibold text-slate-900">{{ __('app.recent_payments') }}</h2>
        <p class="text-xs text-slate-400 mt-0.5">{{ __('app.latest_payments') }}</p>
    </div>
    @if($recentPayments->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.reference') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.order') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.amount') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.method') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.status') }}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('app.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($recentPayments as $payment)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-900">
                        {{ $payment->reference_number ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($payment->order)
                            <a href="{{ route('enduser.orders.show', $payment->order->uuid) }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                                {{ $payment->order->order_no }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-medium text-slate-900">
                        {{ $payment->currency ?? 'SAR' }} {{ number_format($payment->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ ucfirst($payment->payment_method ?? '—') }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $payStatus = $payment->status?->value ?? 'pending';
                            $payBadge = match($payStatus) {
                                'pending'   => ['bg-amber-100 text-amber-700',   __('app.status_pending')],
                                'submitted' => ['bg-blue-100 text-blue-700',     __('app.status_submitted')],
                                'approved'  => ['bg-emerald-100 text-emerald-700',__('app.status_approved')],
                                'rejected'  => ['bg-red-100 text-red-700',       __('app.status_rejected')],
                                'refunded'  => ['bg-red-100 text-red-700',       __('app.status_refunded')],
                                default     => ['bg-slate-100 text-slate-600',    ucfirst($payStatus)],
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payBadge[0] }}">
                            {{ $payBadge[1] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : $payment->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-6 py-8 text-center text-sm text-slate-400">{{ __('app.no_payments') }}</div>
    @endif
</div>

@endsection
