@extends('layouts.admin-app')

@section('title', 'View Quotation – Qimta Admin')
@section('page-title', 'Quotation Details')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.quotations.index') }}" wire:navigate class="text-xs font-medium text-slate-500 hover:text-slate-700 transition">Quotations</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">View</span>
@endsection

@section('content')
    <livewire:admin.quotations.show-quotation :uuid="$uuid" />
@endsection
