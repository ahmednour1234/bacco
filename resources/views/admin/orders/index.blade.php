@extends('layouts.admin-app')

@section('title', 'Orders – Qimta Admin')
@section('page-title', 'Orders')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">Orders</span>
@endsection

@section('content')
    <livewire:admin.orders.index-list />
@endsection
