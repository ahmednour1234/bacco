@extends('layouts.admin-app')

@section('title', 'Edit Product – Qimta Admin')
@section('page-title', 'Edit Product')

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.products.index') }}" wire:navigate class="text-xs text-slate-400 hover:text-emerald-600 transition-colors">{{ __('app.products') }}</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ __('app.edit') }}</span>
@endsection

@section('content')

    <div class="mb-2">
        <h2 class="text-xl font-bold text-slate-900">{{ __('app.edit_product') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('app.update_product_desc') }}</p>
    </div>

    <livewire:admin.products.form :product="$product" />

@endsection
