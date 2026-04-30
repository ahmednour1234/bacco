@extends('layouts.supplier-app')

@section('title', __('app.my_products') . ' – ' . __('app.supplier_portal_title'))
@section('page-title', __('app.my_products'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.supplier_portal') }}</span>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ __('app.my_products') }}</span>
@endsection

@section('content')
@if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

@livewire('supplier.products.index-table')
@endsection
