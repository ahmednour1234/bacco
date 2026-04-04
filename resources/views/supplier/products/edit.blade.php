@extends('layouts.supplier-app')

@section('title', 'Edit Product – Supplier Portal – Qimta')
@section('page-title', 'Edit Product')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Portal</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('supplier.products.index') }}" wire:navigate class="text-xs text-slate-400 hover:text-emerald-600 transition-colors">My Products</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Edit Product</span>
@endsection

@section('content')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Edit Product</h2>
        <p class="mt-1 text-sm text-slate-500">Update your pricing and availability details for this product.</p>
    </div>

    @livewire('supplier.products.form', ['supplierProduct' => $supplierProduct])

@endsection
