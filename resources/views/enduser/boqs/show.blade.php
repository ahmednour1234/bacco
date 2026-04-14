@extends('layouts.enduser-app')

@section('title', __('app.title_boq'))
@section('page-title', __('app.boq_details'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.projects.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('app.projects') }}</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ $boq->boq_no }}</span>
@endsection

@section('content')

<livewire:enduser.boqs.show-boq :uuid="$boq->uuid" />

@endsection
