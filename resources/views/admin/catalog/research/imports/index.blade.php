@extends('layouts.admin-app')

@section('title', 'Product Research Imports')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Product Research — Imports</h1>
            <p class="text-sm text-gray-500 mt-1">Upload a Qimta-style workbook. Each row becomes a Product Family — no prices, no invented products.</p>
        </div>
        @can('catalog.import.create')
        <a href="{{ route('admin.catalog.research.imports.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Import
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-start">File</th>
                    <th class="px-4 py-3 text-start">Sheets</th>
                    <th class="px-4 py-3 text-start">Status</th>
                    <th class="px-4 py-3 text-end">Total</th>
                    <th class="px-4 py-3 text-end">Imported</th>
                    <th class="px-4 py-3 text-end">Duplicate</th>
                    <th class="px-4 py-3 text-end">Failed</th>
                    <th class="px-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($imports as $import)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $import->original_file_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $import->sheets_count }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $import->status->value === 'completed' ? 'bg-emerald-100 text-emerald-700'
                                   : ($import->status->value === 'failed' ? 'bg-red-100 text-red-700'
                                   : ($import->status->value === 'processing' ? 'bg-blue-100 text-blue-700'
                                   : 'bg-gray-100 text-gray-600')) }}">
                                {{ $import->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end text-gray-700">{{ number_format($import->total_rows) }}</td>
                        <td class="px-4 py-3 text-end text-emerald-700">{{ number_format($import->imported_rows) }}</td>
                        <td class="px-4 py-3 text-end text-amber-600">{{ number_format($import->duplicate_rows) }}</td>
                        <td class="px-4 py-3 text-end text-red-600">{{ number_format($import->failed_rows) }}</td>
                        <td class="px-4 py-3 text-end">
                            <div class="inline-flex items-center gap-3">
                                @if(in_array($import->status->value, ['uploaded','mapping_required']))
                                    <a href="{{ route('admin.catalog.research.imports.map', $import->uuid) }}" class="text-emerald-600 hover:underline">Map</a>
                                @endif
                                <a href="{{ route('admin.catalog.research.imports.show', $import->uuid) }}" class="text-gray-600 hover:underline">View</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No imports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $imports->links() }}
</div>
@endsection
