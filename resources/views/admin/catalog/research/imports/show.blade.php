@extends('layouts.admin-app')

@section('title', 'Import Details')

@section('content')
@php
    $cards = [
        ['label' => 'Total Rows',        'value' => $report['total_rows'],              'tone' => 'gray'],
        ['label' => 'Imported',          'value' => $report['imported_rows'],           'tone' => 'emerald'],
        ['label' => 'Duplicates',        'value' => $report['duplicate_rows'],          'tone' => 'amber'],
        ['label' => 'Failed',            'value' => $report['failed_rows'],             'tone' => 'red'],
        ['label' => 'Missing Description','value' => $report['rows_missing_description'],'tone' => 'red'],
        ['label' => 'Ready for Research', 'value' => $report['rows_ready_for_research'], 'tone' => 'emerald'],
        ['label' => 'Requiring Review',   'value' => $report['rows_requiring_review'],   'tone' => 'amber'],
    ];
    $tone = fn($t) => [
        'gray'    => 'text-gray-800',
        'emerald' => 'text-emerald-600',
        'amber'   => 'text-amber-600',
        'red'     => 'text-red-600',
    ][$t] ?? 'text-gray-800';
@endphp

<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $import->original_file_name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Status:
                <span class="font-medium">{{ $import->status->label() }}</span>
                @if($import->completed_at) · completed {{ $import->completed_at->diffForHumans() }} @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('catalog.import.process')
            <form method="POST" action="{{ route('admin.catalog.research.imports.reprocess', $import->uuid) }}">@csrf
                <button class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Reprocess</button>
            </form>
            <form method="POST" action="{{ route('admin.catalog.research.queue.run') }}">@csrf
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Run Queue</button>
            </form>
            @endcan
            <a href="{{ route('admin.catalog.research.imports.index') }}" class="text-sm text-gray-500 hover:underline">← Back to imports</a>
        </div>
    </div>

    @if(session('error'))<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>@endif

    @if($import->status->value === 'failed' && $import->error_message)
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <b>Why it failed:</b> {{ $import->error_message }}
        </div>
    @endif

    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Import report --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        @foreach($cards as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-2 text-2xl font-bold {{ $tone($card['tone']) }}">{{ number_format($card['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 text-sm text-emerald-800">
        Rows marked <b>Ready for Research</b> have at least one manufacturer and can be sent to the research pipeline (Phase 4). No product variants were created during import — only Product Families and raw source rows.
    </div>
</div>
@endsection
