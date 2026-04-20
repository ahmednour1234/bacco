@extends('layouts.supplier-app')

@section('title', __('app.edit_product_title') . ' – ' . __('app.supplier_portal_title'))
@section('page-title', __('app.edit_product_title'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.supplier_portal') }}</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('supplier.products.index') }}" wire:navigate class="text-xs text-slate-400 hover:text-emerald-600 transition-colors">{{ __('app.my_products') }}</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ __('app.edit_product_title') }}</span>
@endsection

@section('content')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">{{ __('app.edit_product_title') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('app.edit_product_desc') }}</p>
    </div>

    @livewire('supplier.products.form', ['supplierProduct' => $supplierProduct])

@endsection
