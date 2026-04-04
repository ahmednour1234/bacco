@extends('layouts.admin-app')

@section('title', $supplier->name . ' – Supplier – Qimta Admin')
@section('page-title', 'Supplier Details')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.suppliers.index') }}" wire:navigate class="text-xs font-medium text-slate-500 hover:text-slate-700 transition">Suppliers</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ $supplier->name }}</span>
@endsection

@section('content')
@if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

<div class="space-y-6 max-w-3xl">

    {{-- Header card --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-lg font-bold text-indigo-600">
                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $supplier->name }}</h1>
                    <p class="text-sm text-slate-500">{{ $supplier->email }}</p>
                    @if($supplier->supplierProfile?->company_name)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $supplier->supplierProfile->company_name }}</p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($supplier->active)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                    </span>
                @endif
                <a href="{{ route('admin.suppliers.edit', $supplier->uuid) }}" wire:navigate
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.suppliers.toggle-status', $supplier->uuid) }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold transition
                               {{ $supplier->active
                                    ? 'border border-red-200 bg-red-50 text-red-600 hover:bg-red-100'
                                    : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                        @if($supplier->active)
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Block
                        @else
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Activate
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">Profile Details</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone</dt>
                <dd class="mt-0.5 text-sm text-slate-700">{{ $supplier->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Division / Role</dt>
                <dd class="mt-0.5 text-sm text-slate-700">{{ $supplier->supplierProfile?->division ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Address</dt>
                <dd class="mt-0.5 text-sm text-slate-700">{{ $supplier->supplierProfile?->address ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">City / Country</dt>
                <dd class="mt-0.5 text-sm text-slate-700">
                    {{ collect([$supplier->supplierProfile?->city, $supplier->supplierProfile?->country])->filter()->implode(', ') ?: '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Products in Catalogue</dt>
                <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ $supplier->supplier_products_count }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Member Since</dt>
                <dd class="mt-0.5 text-sm text-slate-700">{{ $supplier->created_at?->format('M j, Y') }}</dd>
            </div>
        </dl>
    </div>

</div>
@endsection
