@extends('layouts.admin-app')
@section('title', 'BOQ #' . $boq->id . ' — Matching')
@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">BOQ #{{ $boq->id }}</h1>
            <p class="text-sm text-gray-500">Pick the product that satisfies each line, and price it.</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.catalog.research.pricing.boq.rematch', $boq->id) }}">
                @csrf
                <button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Re-match</button>
            </form>
            <a href="{{ route('admin.catalog.research.pricing.boq.index') }}" class="self-center text-sm text-gray-500 hover:underline">← All BOQs</a>
        </div>
    </div>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search lines…"
               class="w-72 rounded-lg border-gray-300 text-sm">
        <button class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Search</button>
    </form>

    @forelse($productItems as $item)
        @php $candidates = $matches[$item->id] ?? collect(); @endphp

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            {{-- The BOQ line as written --}}
            <div class="border-b border-gray-100 px-5 py-3">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="max-w-3xl">
                        <div class="text-sm font-semibold text-gray-800">{{ $item->description }}</div>
                        <div class="mt-1 text-xs text-gray-500">
                            Qty: <span class="font-semibold">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</span>
                            @if($item->unit) · {{ $item->unit->name ?? $item->unit->code ?? '' }} @endif
                            @if($item->brand) · Brand: {{ $item->brand }} @endif
                        </div>
                    </div>
                    @if($candidates->isEmpty())
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">no candidates</span>
                    @endif
                </div>

                {{-- What the parser understood, so a bad match is explainable --}}
                @php $specs = $candidates->first()?->parsed_specs ?? null; @endphp
                @if($specs)
                    <div class="mt-2 flex flex-wrap gap-1 text-xs">
                        @foreach(['size','material','connection','pressure','sku'] as $k)
                            @if(!empty($specs[$k]))
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-gray-600">{{ $k }}: <b>{{ $specs[$k] }}</b></span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Candidate products --}}
            @forelse($candidates as $m)
                <div class="border-b border-gray-50 px-5 py-3 {{ $m->is_selected ? 'bg-emerald-50/60' : '' }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-400">#{{ $m->rank }}</span>
                                <span class="text-sm text-gray-800">{{ Str::limit($m->variant->variant_name ?? '—', 70) }}</span>
                                @if($m->is_selected)
                                    <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-xs font-semibold text-white">selected</span>
                                @endif
                            </div>
                            <div class="mt-0.5 text-xs text-gray-500">
                                {{ $m->variant->manufacturer->name ?? '—' }}
                                @if($m->variant?->manufacturer_sku) · <span class="font-mono">{{ $m->variant->manufacturer_sku }}</span> @endif
                                · {{ $m->match_method?->label() }}
                                · <span class="font-semibold">{{ (float) $m->confidence_score }}%</span>
                            </div>
                            @if(!empty($m->spec_conflicts))
                                <div class="mt-1 text-xs text-amber-700">
                                    ⚠ differs on: {{ implode(', ', array_keys($m->spec_conflicts)) }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($m->unit_price)
                                <span class="rounded-lg bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800">
                                    {{ number_format((float) $m->unit_price, 2) }} {{ $m->currency }}
                                </span>
                            @else
                                <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">no price</span>
                            @endif

                            @unless($m->is_selected)
                                <form method="POST" action="{{ route('admin.catalog.research.pricing.boq.select', $m->id) }}">
                                    @csrf
                                    <button class="rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-900">Select</button>
                                </form>
                            @endunless

                            <button type="button" onclick="document.getElementById('price-{{ $m->id }}').classList.toggle('hidden')"
                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                {{ $m->unit_price ? 'Update price' : '+ Price' }}
                            </button>
                        </div>
                    </div>

                    {{-- Inline pricing: the whole point of this screen --}}
                    <div id="price-{{ $m->id }}" class="hidden mt-3 rounded-lg bg-gray-50 p-3">
                        <form method="POST" action="{{ route('admin.catalog.research.pricing.boq.price', $m->id) }}"
                              class="grid gap-2 md:grid-cols-6">
                            @csrf
                            <input type="number" step="0.01" min="0" name="price" required placeholder="Price"
                                   class="rounded-lg border-gray-300 text-sm">
                            <input type="text" name="currency" value="SAR" maxlength="3"
                                   class="rounded-lg border-gray-300 text-sm">
                            <select name="price_tier" class="rounded-lg border-gray-300 text-sm">
                                @foreach($tiers as $t)
                                    <option value="{{ $t->value }}" @selected($t->value==='retail')>{{ $t->label() }}</option>
                                @endforeach
                            </select>
                            <select name="supplier_id" class="rounded-lg border-gray-300 text-sm">
                                <option value="">— supplier —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <select name="source" class="rounded-lg border-gray-300 text-sm">
                                @foreach($sources as $src)
                                    <option value="{{ $src->value }}" @selected($src->value==='supplier_quote')>{{ $src->label() }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Save &amp; select</button>
                            <input type="url" name="source_url" placeholder="Source URL (optional)"
                                   class="rounded-lg border-gray-300 text-sm md:col-span-6">
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-4 text-sm text-gray-500">
                    No catalog product matched this line.
                </div>
            @endforelse
        </div>
    @empty
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-10 text-center text-gray-500 shadow-sm">
            No product lines on this page — BOQ rows that are headings or contract clauses are hidden.
        </div>
    @endforelse

    {{ $items->links() }}
</div>
@endsection
