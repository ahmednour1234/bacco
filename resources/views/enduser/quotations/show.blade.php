@extends('layouts.enduser-app')

@section('title', 'Quotation – Qimta')
@section('page-title', 'Quotation')

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

<livewire:enduser.quotations.show-quotation :uuid="$quotation->uuid" />

@endsection
