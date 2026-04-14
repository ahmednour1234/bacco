@extends('layouts.admin-app')

@section('title', 'Dashboard – Qimta Admin')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Stats grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

        {{-- Total Quotations --}}
        <a href="{{ route('admin.quotations.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Quotations</p>
                <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalQuotations) }}</p>
            </div>
        </a>

        {{-- Active Orders --}}
        <a href="{{ route('admin.orders.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Active Orders</p>
                <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($activeOrders) }}</p>
            </div>
        </a>

        {{-- Total Clients --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Clients</p>
                <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalClients) }}</p>
            </div>
        </div>

        {{-- Active Projects --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Projects</p>
                <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($activeProjects) }}</p>
            </div>
        </div>
    </div>

    {{-- Recent tables grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">

        {{-- Recent Orders --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</a>
            </div>
            @if($recentOrders->isEmpty())
                <div class="px-6 py-8 text-center">
                    <p class="text-sm text-slate-500">No orders yet.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                        <a href="{{ route('admin.orders.show', $order->uuid) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">#{{ $order->order_number ?? $order->uuid }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $order->client?->name ?? '—' }}</p>
                            </div>
                            <div class="text-right shrink-0 ml-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @switch($order->status?->value)
                                        @case('pending')    bg-yellow-100 text-yellow-700 @break
                                        @case('confirmed')  bg-blue-100 text-blue-700 @break
                                        @case('processing') bg-indigo-100 text-indigo-700 @break
                                        @case('shipped')    bg-cyan-100 text-cyan-700 @break
                                        @case('delivered')  bg-emerald-100 text-emerald-700 @break
                                        @case('completed')  bg-green-100 text-green-700 @break
                                        @case('cancelled')  bg-red-100 text-red-700 @break
                                        @default            bg-slate-100 text-slate-700
                                    @endswitch
                                ">{{ $order->status?->label() ?? '—' }}</span>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $order->created_at?->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Quotations --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Recent Quotations</h2>
                <a href="{{ route('admin.quotations.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</a>
            </div>
            @if($recentQuotations->isEmpty())
                <div class="px-6 py-8 text-center">
                    <p class="text-sm text-slate-500">No quotation requests yet.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentQuotations as $quotation)
                        <a href="{{ route('admin.quotations.show', $quotation->uuid) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">#{{ $quotation->request_number ?? $quotation->uuid }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $quotation->client?->name ?? '—' }}</p>
                            </div>
                            <div class="text-right shrink-0 ml-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @switch($quotation->status?->value)
                                        @case('draft')      bg-slate-100 text-slate-600 @break
                                        @case('submitted')  bg-yellow-100 text-yellow-700 @break
                                        @case('tender')     bg-orange-100 text-orange-700 @break
                                        @case('in_review')  bg-blue-100 text-blue-700 @break
                                        @case('quoted')     bg-indigo-100 text-indigo-700 @break
                                        @case('accepted')   bg-emerald-100 text-emerald-700 @break
                                        @case('rejected')   bg-red-100 text-red-700 @break
                                        @case('cancelled')  bg-red-100 text-red-700 @break
                                        @default            bg-slate-100 text-slate-700
                                    @endswitch
                                ">{{ $quotation->status?->label() ?? '—' }}</span>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $quotation->created_at?->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Welcome card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    Welcome back, {{ auth()->user()->name ?? 'Employee' }}
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    You are signed in as
                    <span class="font-medium text-slate-700">{{ auth()->user()->user_type?->label() ?? 'Employee' }}</span>.
                    Use the sidebar to navigate.
                </p>
            </div>
        </div>
    </div>
@endsection
