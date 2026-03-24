@extends('layouts.admin-app')

@section('title', 'Brands – Qimta Admin')
@section('page-title', 'Brands')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Catalog</span>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Brands</span>
@endsection

@section('content')

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header row --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-slate-500">Manage product brands and their website associations.</p>
        </div>
        <a href="{{ route('admin.brands.create') }}"
              wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Brand
        </a>
    </div>

    {{-- Table --}}
    <livewire:admin.brands.index-table />
@endsection
