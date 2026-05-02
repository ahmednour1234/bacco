@extends('layouts.admin-app')

@section('title', 'Catalog Imports')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Catalog Imports</h1>
        <div class="flex items-center gap-3">
            {{-- Run queue button --}}
            <form method="POST" action="{{ route('admin.catalog.queue.run') }}">
                @csrf
                <button type="submit"
                        onclick="this.disabled=true; this.innerHTML='<svg class=\'animate-spin h-4 w-4\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8v8z\'></path></svg> Starting…'; this.form.submit();"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/>
                    </svg>
                    Run Queue
                </button>
            </form>
            <a href="{{ route('admin.catalog.imports.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Import
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">File</th>
                    <th class="px-4 py-3 text-left">Catalog</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Progress</th>
                    <th class="px-4 py-3 text-right">Rows</th>
                    <th class="px-4 py-3 text-left">Uploaded</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($imports as $import)
                    @php
                        $statusColors = [
                            'pending'    => 'bg-yellow-100 text-yellow-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'completed'  => 'bg-green-100 text-green-800',
                            'failed'     => 'bg-red-100 text-red-800',
                        ];
                        $color = $statusColors[$import->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $import->id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 max-w-xs truncate">{{ $import->file_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $import->catalog?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $color }}">
                                {{ ucfirst($import->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 w-40">
                            @if($import->total_rows > 0)
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-green-500 h-2 rounded-full transition-all"
                                         style="width: {{ $import->progressPercent() }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 mt-0.5 block">{{ $import->progressPercent() }}%</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            <div class="text-xs text-gray-500">
                                <div>Processed: {{ number_format($import->processed_rows) }}</div>
                                <div class="text-green-600">In: {{ number_format($import->inserted_rows) }}</div>
                                <div class="text-blue-600">Up: {{ number_format($import->updated_rows) }}</div>
                                @if($import->failed_rows > 0)
                                    <div class="text-red-600">Fail: {{ number_format($import->failed_rows) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $import->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.catalog.imports.show', $import->id) }}"
                               class="text-blue-600 hover:underline text-xs">View</a>
                            @if($import->failed_rows > 0)
                                <a href="{{ route('admin.catalog.imports.failed-rows', $import->id) }}"
                                   class="text-red-600 hover:underline text-xs">Failed rows</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">No imports yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $imports->links() }}</div>
</div>
@endsection
