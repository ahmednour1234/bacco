@extends('layouts.admin-app')

@section('title', 'New Research Import')

@section('content')
<div class="p-6 max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">New Research Import</h1>
        <p class="text-sm text-gray-500 mt-1">Upload XLSX, XLS or CSV. The original file is stored and never deleted.</p>
    </div>

    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.catalog.research.imports.store') }}"
          enctype="multipart/form-data"
          class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Excel / CSV file</label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                   class="block w-full text-sm text-gray-600 file:me-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100" />
            <p class="text-xs text-gray-400 mt-1">Max 100 MB. Next step: choose the sheet and map columns.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                Upload & Continue
            </button>
            <a href="{{ route('admin.catalog.research.imports.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
