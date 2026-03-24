@extends('layouts.admin-app')

@section('title', 'Add Brand – Qimta Admin')
@section('page-title', 'Add Brand')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Catalog</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.brands.index') }}" class="text-xs text-slate-400 hover:text-emerald-600 transition-colors">Brands</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Add</span>
@endsection

@section('content')
<livewire:admin.brands.form />
@endsection
