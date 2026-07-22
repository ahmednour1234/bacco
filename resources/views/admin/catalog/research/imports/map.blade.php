@extends('layouts.admin-app')

@section('title', 'Map Columns')

@section('content')
<div class="p-6 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Map Columns</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $import->original_file_name }} — match each spreadsheet column to a catalog field. <b>Item Description is required.</b></p>
    </div>

    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Auto-detection notice --}}
    @php $descMapped = in_array('item_description', $savedMapping, true); @endphp
    <div class="rounded-lg border px-4 py-3 text-sm {{ $descMapped ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
        Detected header row <b>{{ $headerRow }}</b> and pre-filled the column mapping automatically.
        @if($descMapped)
            ✓ Item Description is mapped — review the columns below and click <b>Confirm &amp; Import</b>.
        @else
            ⚠ Could not auto-detect <b>Item Description</b>. Adjust the header row (try 2, 3 or 4) and map it manually before importing.
        @endif
    </div>

    {{-- Sheet + header row selection (GET reloads preview) --}}
    <form method="GET" action="{{ route('admin.catalog.research.imports.map', $import->uuid) }}"
          class="flex flex-wrap items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sheet</label>
            <select name="sheet" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @foreach($sheetNames as $name)
                    <option value="{{ $name }}" @selected($name === $currentSheet)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Header row</label>
            <input type="number" name="header_row" min="1" max="50" value="{{ $headerRow }}"
                   class="w-24 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <button type="submit" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Reload preview</button>
    </form>

    <form method="POST" action="{{ route('admin.catalog.research.imports.process', $import->uuid) }}" class="space-y-6">
        @csrf
        <input type="hidden" name="sheet" value="{{ $currentSheet }}">
        <input type="hidden" name="header_row" value="{{ $headerRow }}">

        {{-- Mapping grid --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Column mapping</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($preview['headers'] as $header)
                    @continue($header === '')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1 truncate" title="{{ $header }}">{{ $header }}</label>
                        <select name="mapping[{{ $header }}]"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">— ignore —</option>
                            @foreach($targetFields as $key => $meta)
                                <option value="{{ $key }}" @selected(($savedMapping[$header] ?? null) === $key)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Preview (first 20 rows) --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Preview — first {{ count($preview['rows']) }} rows</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            @foreach($preview['headers'] as $header)
                                <th class="px-3 py-2 text-start whitespace-nowrap">{{ $header ?: '—' }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($preview['rows'] as $row)
                            <tr>
                                @foreach($row as $cell)
                                    <td class="px-3 py-2 text-gray-700 max-w-xs truncate" title="{{ $cell }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                Confirm & Import
            </button>
            <a href="{{ route('admin.catalog.research.imports.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
