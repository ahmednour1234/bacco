@extends('layouts.admin-app')

@section('title', __('app.boqs') . ' – ' . ($boq->boq_no ?? '') . ' – Qimta Admin')
@section('page-title', __('app.boqs'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.boqs.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 transition">{{ __('app.boqs') }}</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ $boq->boq_no }}</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header Card --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">{{ $boq->boq_no }}</h2>
                <p class="mt-1 text-sm text-slate-400">{{ __('app.created') }}: {{ $boq->created_at?->format('M j, Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @php
                    $statusColors = [
                        'draft'     => 'bg-slate-100 text-slate-700',
                        'submitted' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-600',
                    ];
                    $color = $statusColors[$boq->status->value ?? ''] ?? 'bg-slate-100 text-slate-700';
                    
                    $typeColors = [
                        'tender'  => 'bg-blue-100 text-blue-700',
                        'awarded' => 'bg-emerald-100 text-emerald-700',
                    ];
                    $typeColor = $typeColors[$boq->type->value ?? ''] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold {{ $color }}">
                    {{ ucfirst($boq->status->value ?? '') }}
                </span>
                <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold {{ $typeColor }}">
                    {{ $boq->type?->label() ?? '—' }}
                </span>
                <a href="{{ route('admin.boqs.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('app.back') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Project Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-slate-700">{{ __('app.project') }}</h3>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.name') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $boq->project?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Client Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-50">
                    <svg class="h-4 w-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-slate-700">{{ __('app.client') }}</h3>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.name') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $boq->client?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.email') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $boq->client?->email ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Notes --}}
    @if($boq->notes)
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center gap-2.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </span>
            <h3 class="text-sm font-bold text-slate-700">{{ __('app.notes') }}</h3>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">{{ $boq->notes }}</p>
    </div>
    @endif

    {{-- BOQ Items Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-slate-700">{{ __('app.items') }}</h3>
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-600">{{ $boq->items->count() }}</span>
            </div>
        </div>

        @if($boq->items->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                <svg class="h-10 w-10 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-medium text-slate-400">{{ __('app.no_items_found') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400 w-12">#</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.description') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400 w-24">{{ __('app.quantity') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400 w-28">{{ __('app.category') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400 w-28">{{ __('app.brand') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400 w-24">{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($boq->items as $idx => $item)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-4 text-slate-400 text-xs font-mono">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $item->description ?: '—' }}</td>
                                <td class="px-5 py-4 text-slate-700">
                                    <span class="font-bold">{{ number_format((float)$item->quantity, 0) }}</span>
                                    @if($item->unit)
                                        <span class="text-xs text-slate-400 uppercase ms-1">{{ $item->unit->name ?? '' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-600">{{ $item->category ?: '—' }}</td>
                                <td class="px-5 py-4 text-xs text-slate-600">{{ $item->brand ?: '—' }}</td>
                                <td class="px-5 py-4">
                                    @php
                                        $itemStatusColors = [
                                            'pending'  => 'bg-slate-100 text-slate-600',
                                            'sourcing' => 'bg-blue-100 text-blue-700',
                                            'sourced'  => 'bg-emerald-100 text-emerald-700',
                                            'rejected' => 'bg-red-100 text-red-600',
                                        ];
                                        $ic = $itemStatusColors[$item->status->value ?? ''] ?? 'bg-slate-100 text-slate-600';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $ic }}">
                                        {{ ucfirst($item->status->value ?? '') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
