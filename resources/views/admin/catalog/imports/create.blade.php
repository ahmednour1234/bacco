@extends('layouts.admin-app')

@section('title', 'Upload Catalog File')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.catalog.imports.index') }}"
           class="text-gray-400 hover:text-gray-600 transition">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Upload Catalog File</h1>
    </div>

    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm space-y-1">
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.catalog.imports.store') }}"
          enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        {{-- Catalog selector (optional) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Catalog
                <span class="ml-1 text-xs font-normal text-gray-400">(optional — auto-created from filename if left blank)</span>
            </label>
            <select name="catalog_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">— Auto-create from filename —</option>
                @foreach($catalogs as $catalog)
                    <option value="{{ $catalog->id }}" @selected(old('catalog_id') == $catalog->id)>
                        {{ $catalog->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- File input --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Excel / CSV files <span class="text-red-500">*</span>
            </label>
            <div id="drop-zone"
                 class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-10 text-center cursor-pointer hover:border-green-400 hover:bg-green-50 transition"
                 onclick="document.getElementById('file-input').click()">
                <svg class="mb-3 h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <p class="text-sm font-medium text-gray-600">Click or drag &amp; drop your files here</p>
                <p class="mt-1 text-xs text-gray-400">xlsx, xls, csv — up to 7 files, max 100 MB each</p>
                <input id="file-input" type="file" name="files[]"
                       accept=".xlsx,.xls,.csv" multiple class="hidden"
                       onchange="showFileList(this)">
            </div>
            <ul id="file-list" class="mt-3 space-y-1 text-xs text-gray-600"></ul>
        </div>

        {{-- Notes --}}
        <div class="rounded-lg bg-blue-50 border border-blue-100 text-blue-800 px-4 py-3 text-xs space-y-1">
            <p class="font-semibold">Expected Excel format:</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li>Heading row must be on <strong>row 4</strong></li>
                <li>Required columns: <code>Qimta Code, Division, Category, Item Description, Sub-Type, Product Name, Type of Material, Size, Unit, Lead Time</code></li>
                <li>Data starts on <strong>row 5</strong></li>
            </ul>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.catalog.imports.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-green-700 transition">
                Upload &amp; Queue
            </button>
        </div>
    </form>
</div>

<script>
function showFileList(input) {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    Array.from(input.files).forEach(f => {
        const li = document.createElement('li');
        li.className = 'flex items-center gap-2';
        li.innerHTML = `<svg class="h-3 w-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
        </svg>${f.name} <span class="text-gray-400">(${(f.size/1024/1024).toFixed(1)} MB)</span>`;
        list.appendChild(li);
    });
}

// Drag-and-drop
const zone = document.getElementById('drop-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-green-400'); });
zone.addEventListener('dragleave', () => zone.classList.remove('border-green-400'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('border-green-400');
    const input = document.getElementById('file-input');
    input.files = e.dataTransfer.files;
    showFileList(input);
});
</script>
@endsection
