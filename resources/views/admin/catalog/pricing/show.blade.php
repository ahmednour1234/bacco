@extends('layouts.admin-app')
@section('title', 'Prices — ' . ($variant->variant_name ?: 'Variant'))
@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $variant->variant_name ?: 'Variant' }}</h1>
            <p class="text-sm text-gray-500">
                {{ $variant->manufacturer->name ?? '—' }}
                @if($variant->manufacturer_sku)
                    · <span class="font-mono">{{ $variant->manufacturer_sku }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.catalog.research.pricing.index') }}" class="text-sm text-gray-500 hover:underline">← Back to pricing</a>
    </div>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Existing prices, grouped so tiers read side by side --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3 text-sm font-semibold text-gray-700">Current prices</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Tier</th>
                    <th class="px-4 py-2 text-start">Supplier</th>
                    <th class="px-4 py-2 text-end">Price</th>
                    <th class="px-4 py-2 text-center">Qty band</th>
                    <th class="px-4 py-2 text-start">Source</th>
                    <th class="px-4 py-2 text-center">Confidence</th>
                    <th class="px-4 py-2 text-end"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($prices as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <span class="font-semibold text-gray-800">{{ $p->price_tier?->label() }}</span>
                            @if($p->price_tier && ! $p->price_tier->isQuotable())
                                <span class="ms-1 text-xs text-gray-400">(reference)</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $p->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-end font-semibold text-gray-800">
                            {{ number_format((float) $p->price, 2) }} {{ $p->currency }}
                        </td>
                        <td class="px-4 py-2 text-center text-gray-600">
                            {{ $p->min_quantity }}{{ $p->max_quantity ? '–'.$p->max_quantity : '+' }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-gray-700">{{ $p->source?->label() }}</span>
                            @if($p->needsEstimateWarning())
                                <span class="ms-1 rounded bg-sky-100 px-1.5 py-0.5 text-xs font-semibold text-sky-700">not binding</span>
                            @endif
                            @if($p->source_url)
                                <a href="{{ $p->source_url }}" target="_blank" rel="noopener"
                                   class="ms-1 text-xs text-emerald-600 hover:underline">source ↗</a>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $p->confidence?->badgeClass() }}">
                                {{ $p->confidence?->label() }}
                            </span>
                            @if($p->isStale())
                                <span class="ms-1 rounded bg-gray-200 px-1.5 py-0.5 text-xs text-gray-600">stale</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-end">
                            <form method="POST" action="{{ route('admin.catalog.research.pricing.destroy', [$variant->uuid, $p->id]) }}"
                                  onsubmit="return confirm('Remove this price?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        No prices yet. Add a supplier price below.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Manual entry: the route to coverage for categories the scraper misses --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3 text-sm font-semibold text-gray-700">Add a price</div>
        <form method="POST" action="{{ route('admin.catalog.research.pricing.store', $variant->uuid) }}" class="grid gap-4 p-5 md:grid-cols-3">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600">Price <span class="text-red-500">*</span></label>
                <input type="number" step="0.0001" min="0" name="price" value="{{ old('price') }}" required
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Currency</label>
                <input type="text" name="currency" value="{{ old('currency', 'SAR') }}" maxlength="3"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Tier <span class="text-red-500">*</span></label>
                <select name="price_tier" required class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                    @foreach($tiers as $t)
                        <option value="{{ $t->value }}" @selected(old('price_tier')===$t->value)>{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Supplier</label>
                <select name="supplier_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                    <option value="">— none —</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Source <span class="text-red-500">*</span></label>
                <select name="source" required class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                    @foreach($sources as $s)
                        <option value="{{ $s->value }}" @selected(old('source')===$s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Trust level follows the source automatically.</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Source URL</label>
                <input type="url" name="source_url" value="{{ old('source_url') }}" placeholder="https://…"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Min quantity (MOQ)</label>
                <input type="number" min="1" name="min_quantity" value="{{ old('min_quantity', 1) }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Max quantity</label>
                <input type="number" min="1" name="max_quantity" value="{{ old('max_quantity') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Unit</label>
                <input type="text" name="price_unit" value="{{ old('price_unit') }}" placeholder="each, meter, box"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Valid from</label>
                <input type="date" name="valid_from" value="{{ old('valid_from') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Valid to</label>
                <input type="date" name="valid_to" value="{{ old('valid_to') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Lead time (days)</label>
                <input type="number" min="0" name="lead_time_days" value="{{ old('lead_time_days') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-gray-600">Notes</label>
                <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="md:col-span-3">
                <button class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Save price
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
