@extends('layouts.admin-app')

@section('title', __('app.brands') . ' – Qimta Admin')
@section('page-title', __('app.brands'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.catalog') }}</span>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ __('app.brands') }}</span>
@endsection

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Header row --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-slate-500">{{ __('app.manage_brands_desc') }}</p>
        </div>
        <a href="{{ route('admin.brands.create') }}"
              wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_brand') }}
        </a>
    </div>

    {{-- Import Excel --}}
    <div class="mb-6 flex flex-wrap items-center gap-3" x-data="{ open: false }">
        <a href="{{ route('admin.brands.template') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ __('app.download_template') }}
        </a>

        <button @click="open = !open" type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            {{ __('app.import_excel') }}
        </button>

        <form x-show="open" x-transition action="{{ route('admin.brands.import') }}" method="POST" enctype="multipart/form-data"
              class="inline-flex items-center gap-2">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1 file:text-sm file:font-medium file:text-emerald-700">
            <button type="submit"
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition shadow-sm">
                {{ __('app.import') }}
            </button>
        </form>
    </div>

    {{-- Table --}}
    <livewire:admin.brands.index-table />
@endsection
