@extends('layouts.admin-app')
@section('title', 'Product Pricing')
@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Product Pricing</h1>
            <p class="text-sm text-gray-500">Prices attach to real catalog variants. The catalog itself stores no prices.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.catalog.research.pricing.matches') }}"
               class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Match Review</a>
            <a href="{{ route('admin.catalog.research.pricing.suppliers') }}"
               class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Suppliers</a>
        </div>
    </div>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>@endif

    {{-- Coverage: the number that actually matters for quoting --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
        @foreach([
            ['Variants',  $stats['variants'],  'text-gray-800'],
            ['Priced',    $stats['priced'],    'text-emerald-600'],
            ['Unpriced',  $stats['unpriced'],  'text-amber-600'],
            ['Prices',    $stats['prices'],    'text-sky-600'],
            ['Suppliers', $stats['suppliers'], 'text-gray-800'],
        ] as [$label, $value, $class])
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                <div class="mt-1 text-2xl font-bold {{ $class }}">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-gray-700">Pricing coverage</span>
            <span class="font-bold text-gray-800">{{ $stats['coverage'] }}%</span>
        </div>
        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $stats['coverage']) }}%"></div>
        </div>
        <p class="mt-2 text-xs text-gray-500">
            Scraped sources cover electrical/ELV categories only. Everything else — fire protection,
            valves, plumbing — reaches coverage through manual supplier prices.
        </p>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-xs font-medium text-gray-500">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name or SKU"
                   class="mt-1 w-64 rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Priced</label>
            <select name="priced" class="mt-1 rounded-lg border-gray-300 text-sm">
                <option value="">All</option>
                <option value="no"  @selected(request('priced')==='no')>Unpriced only</option>
                <option value="yes" @selected(request('priced')==='yes')>Priced only</option>
            </select>
        </div>
        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
        @if(request()->hasAny(['q','priced']))
            <a href="{{ route('admin.catalog.research.pricing.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Variants --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Product</th>
                    <th class="px-4 py-2 text-start">Manufacturer</th>
                    <th class="px-4 py-2 text-start">SKU</th>
                    <th class="px-4 py-2 text-center">Prices</th>
                    <th class="px-4 py-2 text-end">Action</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($variants as $v)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-800">{{ Str::limit($v->variant_name, 60) ?: '—' }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $v->manufacturer->name ?? '—' }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $v->manufacturer_sku ?: '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($v->prices_count > 0)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ $v->prices_count }}</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">none</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-end">
                            <a href="{{ route('admin.catalog.research.pricing.show', $v->uuid) }}"
                               class="text-emerald-600 hover:underline">{{ $v->prices_count ? 'Manage' : 'Add price' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No variants found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $variants->links() }}</div>
    </div>
</div>
@endsection
