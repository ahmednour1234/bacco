@extends('layouts.enduser-app')

@section('title', __('app.title_new_quotation'))
@section('page-title', __('app.new_quotation'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.quotations.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('app.quotations') }}</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.new_quotation') }}</span>
@endsection

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">{{ __('app.new_quotation') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('app.fill_quotation_details') }}</p>
</div>

<livewire:enduser.quotations.create-quotation :quotationId="null" />

@endsection
