@extends('layouts.admin-app')

@section('title', 'Quotations – Qimta Admin')
@section('page-title', 'Quotations')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Quotations</span>
@endsection

@section('content')
    <livewire:admin.quotations.index-table />
@endsection
