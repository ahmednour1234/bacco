@extends('layouts.enduser-app')

@section('title', 'New Quotation – Qimta')
@section('page-title', 'New Quotation')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.quotations.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Quotations</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">New Quotation</span>
@endsection

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">New Quotation</h1>
    <p class="mt-1 text-sm text-slate-500">Fill in the technical and financial details to generate a project quotation.</p>
</div>

<livewire:enduser.quotations.create-quotation :quotationId="null" />

@endsection
