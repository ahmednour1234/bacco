@extends('layouts.supplier-app')

@section('title', 'Add Product – Supplier Portal – Qimta')
@section('page-title', 'Add Product')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Portal</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('supplier.products.index') }}" wire:navigate class="text-xs text-slate-400 hover:text-emerald-600 transition-colors">My Products</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Add Product</span>
@endsection

@section('content')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Add New Product</h2>
        <p class="mt-1 text-sm text-slate-500">Fill in the information below to add a product to your supplier catalogue.</p>
    </div>

    @livewire('supplier.products.form')

@endsection
