@extends('layouts.admin-app')

@section('title', 'Catalog Products')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Catalog Products</h1>
        <a href="{{ route('admin.catalog.imports.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-700 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            Import File
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.catalog.products.index') }}"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

            {{-- Catalog --}}
            <div class="col-span-2 md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Catalog</label>
                <select name="catalog_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All catalogs</option>
                    @foreach($catalogs as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['catalog_id'] ?? '') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select name="category_id"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(($filters['category_id'] ?? '') == $c->id)>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Division --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Division</label>
                <select name="division"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All</option>
                    @foreach($divisions as $d)
                        <option value="{{ $d }}" @selected(($filters['division'] ?? '') === $d)>{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sub-Type --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sub-Type</label>
                <select name="sub_type"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All</option>
                    @foreach($subTypes as $s)
                        <option value="{{ $s }}" @selected(($filters['sub_type'] ?? '') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Unit --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Unit</label>
                <select name="unit"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" @selected(($filters['unit'] ?? '') === $u)>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Search --}}
            <div class="flex items-end">
                <div class="flex w-full gap-2">
                    <input type="text" name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Search products…"
                           class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <button type="submit"
                            class="rounded-lg bg-green-600 px-3 py-1.5 text-white text-sm hover:bg-green-700 transition">
                        Go
                    </button>
                </div>
            </div>
        </div>

        @if(array_filter($filters))
            <div class="mt-3">
                <a href="{{ route('admin.catalog.products.index') }}"
                   class="text-xs text-gray-500 hover:text-red-600 hover:underline">
                    ✕ Clear filters
                </a>
            </div>
        @endif
    </form>

    {{-- Results count --}}
    <p class="text-sm text-gray-500">
        {{ number_format($products->total()) }} product(s) found
    </p>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-3 py-3 text-left">Qimta Code</th>
                    <th class="px-3 py-3 text-left">Division</th>
                    <th class="px-3 py-3 text-left">Category</th>
                    <th class="px-3 py-3 text-left">Product Name</th>
                    <th class="px-3 py-3 text-left">Sub-Type</th>
                    <th class="px-3 py-3 text-left">Material</th>
                    <th class="px-3 py-3 text-left">Size</th>
                    <th class="px-3 py-3 text-left">Unit</th>
                    <th class="px-3 py-3 text-left">Lead Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-3 py-2 font-mono text-gray-700 whitespace-nowrap">{{ $product->qimta_code ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $product->division ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $product->product_name ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $product->sub_type ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $product->type_of_material ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $product->size ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $product->unit ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $product->lead_time ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                            No products found. <a href="{{ route('admin.catalog.imports.create') }}" class="text-green-600 hover:underline">Import a file</a> to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $products->appends($filters)->links() }}</div>
</div>
@endsection
