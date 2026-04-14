@extends('layouts.enduser-app')

@section('title', __('app.title_edit_quotation'))
@section('page-title', __('app.edit_quotation'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.quotations.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('app.quotations') }}</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}" class="text-xs text-slate-400 hover:text-slate-600">{{ $quotation->quotation_no }}</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.edit') }}</span>
@endsection

@section('content')

<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ __('app.edit_quotation') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('app.update_quotation_details') }}</p>
    </div>
    <a
        href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back_to_quotation') }}
    </a>
</div>

<livewire:enduser.quotations.create-quotation :quotationId="$quotation->id" />

@endsection
