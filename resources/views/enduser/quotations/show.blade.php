@extends('layouts.enduser-app')

@section('title', 'Edit Quotation – Qimta')
@section('page-title', 'Edit Quotation')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.quotations.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Quotations</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ $quotation->quotation_no }}</span>
@endsection

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $quotation->quotation_no }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $quotation->project_name }}</p>
    </div>
    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
        @if($quotation->status->value === 'draft') bg-amber-100 text-amber-700
        @elseif($quotation->status->value === 'submitted') bg-blue-100 text-blue-700
        @else bg-slate-100 text-slate-600 @endif">
        {{ $quotation->status->label() }}
    </span>
</div>

<livewire:enduser.quotations.create-quotation :quotationId="$quotation->id" />

@endsection
