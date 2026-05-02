@extends('layouts.admin-app')

@section('title', 'Add Article')

@section('content')
<div class="px-6 py-8 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.articles.index') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 hover:bg-slate-50 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Add Article</h1>
            <p class="mt-0.5 text-sm text-slate-500">Create a new bilingual article.</p>
        </div>
    </div>

    <livewire:admin.articles.form />
</div>
@endsection
