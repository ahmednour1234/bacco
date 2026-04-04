@extends('layouts.enduser-app')

@section('title', 'Order – Qimta')
@section('page-title', 'Order')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('enduser.orders.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Orders</a>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ $order->order_no }}</span>
@endsection

@section('content')

<livewire:enduser.orders.show-order :uuid="$order->uuid" />

@endsection
