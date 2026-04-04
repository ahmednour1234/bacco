@extends('layouts.supplier-app')

@section('title', 'Dashboard – Supplier Portal – Qimta')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome banner --}}
    <div class="rounded-2xl bg-emerald-600 px-6 py-5 text-white shadow-lg shadow-emerald-500/20">
        <p class="text-sm font-medium text-emerald-200">Welcome back,</p>
        <h2 class="text-2xl font-bold mt-0.5">{{ auth()->user()->name }}</h2>
        <p class="text-emerald-200 text-sm mt-1">Manage your product catalogue from the supplier portal.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total Products --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Products</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $totalProducts }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100">
                    <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Active Products --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Active</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $activeProducts }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Inactive Products --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Inactive</p>
                    <p class="text-3xl font-bold text-slate-500 mt-1">{{ $inactiveProducts }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick action --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('supplier.products.create') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Product
        </a>
        <a href="{{ route('supplier.products.index') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            View All Products
        </a>
    </div>

</div>
@endsection
