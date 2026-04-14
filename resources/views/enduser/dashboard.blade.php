@extends('layouts.enduser-app')

@section('title', 'Dashboard – Qimta')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">Dashboard</span>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════
     STATS CARDS
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">

    {{-- Total Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['total_quotations'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Quotations</p>
    </a>

    {{-- Active Quotations --}}
    <a href="{{ route('enduser.quotations.index') }}" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['active_quotations'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Active Quotations</p>
    </a>

    {{-- Active Orders --}}
    <a href="{{ route('enduser.orders.index') }}" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['active_orders'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Active Orders</p>
    </a>

    {{-- Active Projects --}}
    <a href="{{ route('enduser.projects.index') }}" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-violet-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['active_projects'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Active Projects</p>
    </a>

    {{-- Completed Projects --}}
    <a href="{{ route('enduser.projects.index') }}" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
        <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['completed_projects'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Completed Projects</p>
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     TWO-COLUMN ROW: Track Quotations + Accepted Quotations
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ─────────────────────────────────────────────────────
         TRACK QUOTATIONS (wider column)
    ───────────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Track Quotations</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest quotation requests</p>
            </div>
            <a href="{{ route('enduser.quotations.index') }}"
               class="text-xs font-medium text-emerald-600 hover:text-emerald-700
                      bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                View all
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Quotation ID</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Items</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentQuotations as $quotation)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-900">#{{ $quotation->id }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $quotation->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $quotation->items_count }} items
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $status = $quotation->status?->value ?? 'pending';
                                $badge = match($status) {
                                    'accepted', 'approved' => ['bg-emerald-100 text-emerald-700', 'Accepted'],
                                    'submitted'            => ['bg-yellow-100 text-yellow-700',   'Submitted'],
                                    'tender'               => ['bg-orange-100 text-orange-700',   'Tender'],
                                    'pending'              => ['bg-amber-100 text-amber-700',     'Pending'],
                                    'rejected', 'cancelled'=> ['bg-red-100 text-red-700',         'Rejected'],
                                    'in_review'            => ['bg-blue-100 text-blue-700',       'In Review'],
                                    'quoted'               => ['bg-indigo-100 text-indigo-700',   'Quoted'],
                                    'draft'                => ['bg-slate-100 text-slate-600',      'Draft'],
                                    default                => ['bg-slate-100 text-slate-600',      ucfirst($status)],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                View →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">
                            No quotation requests yet.
                            <a href="{{ route('enduser.quotations.create') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Create one →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────
         ACCEPTED QUOTATIONS (narrower column)
    ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Accepted Quotations</h2>
                <p class="text-xs text-slate-400 mt-0.5">Ready for order</p>
            </div>
            @if($stats['accepted_quotations'] > 0)
            <span class="text-xs font-semibold bg-emerald-500 text-white w-6 h-6 rounded-full flex items-center justify-center">
                {{ $stats['accepted_quotations'] }}
            </span>
            @endif
        </div>

        {{-- List --}}
        <div class="divide-y divide-slate-100">
            @forelse($acceptedQuotations as $quotation)
            <div class="px-6 py-4 hover:bg-slate-50/60 transition-colors">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-900 truncate">#{{ $quotation->id }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $quotation->created_at->format('M d, Y') }} · {{ $quotation->items_count }} items
                        </p>
                    </div>
                    <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                       class="text-xs font-medium text-white bg-emerald-500 hover:bg-emerald-600
                              px-3 py-1.5 rounded-lg transition-colors shrink-0">
                        Order
                    </a>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-slate-400">No accepted quotations yet.</p>
            </div>
            @endforelse
        </div>

        {{-- Footer link --}}
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
            <a href="{{ route('enduser.quotations.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                View all accepted →
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     RECENT ORDERS
══════════════════════════════════════════════════════════ --}}
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Recent Orders</h2>
            <p class="text-xs text-slate-400 mt-0.5">Your latest orders</p>
        </div>
        <a href="{{ route('enduser.orders.index') }}"
           class="text-xs font-medium text-emerald-600 hover:text-emerald-700
                  bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
            View all
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Order No</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Total</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-medium text-slate-900">{{ $order->order_no }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ $order->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 font-medium text-slate-900">
                        {{ $order->currency ?? 'SAR' }} {{ number_format($order->grand_total, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $orderStatus = $order->status?->value ?? 'pending';
                            $orderBadge = match($orderStatus) {
                                'pending'    => ['bg-yellow-100 text-yellow-700',  'Pending'],
                                'confirmed'  => ['bg-blue-100 text-blue-700',      'Confirmed'],
                                'processing' => ['bg-indigo-100 text-indigo-700',  'Processing'],
                                'shipped'    => ['bg-cyan-100 text-cyan-700',      'Shipped'],
                                'delivered'  => ['bg-emerald-100 text-emerald-700','Delivered'],
                                'completed'  => ['bg-green-100 text-green-700',    'Completed'],
                                'cancelled'  => ['bg-red-100 text-red-700',        'Cancelled'],
                                'refunded'   => ['bg-red-100 text-red-700',        'Refunded'],
                                default      => ['bg-slate-100 text-slate-600',     ucfirst($orderStatus)],
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $orderBadge[0] }}">
                            {{ $orderBadge[1] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('enduser.orders.show', $order->uuid) }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                            View →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">
                        No orders yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ACTIVE PROJECTS ROW
══════════════════════════════════════════════════════════ --}}
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Active Projects</h2>
            <p class="text-xs text-slate-400 mt-0.5">Your ongoing construction projects</p>
        </div>
        <a href="{{ route('enduser.projects.index') }}"
           class="text-xs font-medium text-emerald-600 hover:text-emerald-700
                  bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
            View all
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Project</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Start Date</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($activeProjects as $project)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $project->name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">#{{ $project->project_no }}</p>
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $projStatus = $project->status?->value ?? 'pending';
                            $projBadge = match($projStatus) {
                                'active'    => ['bg-blue-100 text-blue-700',    'Active'],
                                'pending'   => ['bg-amber-100 text-amber-700',  'Pending'],
                                'on_hold'   => ['bg-orange-100 text-orange-700','On Hold'],
                                'completed' => ['bg-green-100 text-green-700',  'Completed'],
                                'cancelled' => ['bg-red-100 text-red-700',      'Cancelled'],
                                default     => ['bg-slate-100 text-slate-600',   ucfirst($projStatus)],
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $projBadge[0] }}">
                            {{ $projBadge[1] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('enduser.projects.show', $project->uuid) }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                            View →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">
                        No active projects yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
