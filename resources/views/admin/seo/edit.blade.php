@extends('layouts.admin-app')

@section('title', __('app.seo_edit'))

@section('content')
<div class="px-6 py-8 max-w-4xl mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.seo.index') }}" wire:navigate
           class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition hover:bg-slate-200">
            <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $seo->label ?: $seo->route_name }}</h1>
            <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $seo->route_name }}</p>
        </div>
    </div>

    <livewire:admin.seo.form :seo="$seo" />
</div>
@endsection
