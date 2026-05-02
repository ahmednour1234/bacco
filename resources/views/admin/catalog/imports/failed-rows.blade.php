@extends('layouts.admin-app')

@section('title', 'Failed Rows — Import #' . $import->id)

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.catalog.imports.show', $import->id) }}"
           class="text-gray-400 hover:text-gray-600 transition">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Failed Rows</h1>
            <p class="text-sm text-gray-500">Import #{{ $import->id }} — {{ $import->file_name }}</p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left w-24">Row #</th>
                    <th class="px-4 py-3 text-left">Error</th>
                    <th class="px-4 py-3 text-left">Row Data</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($failedRows as $row)
                    <tr class="hover:bg-red-50 transition">
                        <td class="px-4 py-3 font-mono text-gray-700">{{ $row->row_number }}</td>
                        <td class="px-4 py-3 text-red-700 max-w-xs">{{ $row->error_message }}</td>
                        <td class="px-4 py-3">
                            @php $data = is_array($row->row_data) ? $row->row_data : json_decode($row->row_data, true); @endphp
                            <details>
                                <summary class="cursor-pointer text-xs text-blue-600 hover:underline select-none">
                                    Show raw data
                                </summary>
                                <pre class="mt-2 overflow-x-auto rounded bg-gray-100 p-2 text-xs text-gray-700 max-w-lg">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400">No failed rows.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $failedRows->links() }}</div>
</div>
@endsection
