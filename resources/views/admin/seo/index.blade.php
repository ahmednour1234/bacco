@extends('layouts.admin-app')

@section('title', __('app.seo_pages'))

@section('content')
<div class="px-6 py-8 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('app.seo_pages') }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ __('app.seo_pages_desc') }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <livewire:admin.seo.index-table />
</div>
@endsection
