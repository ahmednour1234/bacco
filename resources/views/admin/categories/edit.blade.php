@extends('layouts.admin-app')

@section('title', 'Edit Category – Qimta Admin')
@section('page-title', 'Edit Category')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Catalog</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.categories.index') }}" class="text-xs text-slate-400 hover:text-emerald-600 transition-colors">Categories</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Edit</span>
@endsection

@section('content')
<livewire:admin.categories.form :category="$category" />
@endsection
