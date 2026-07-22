@extends('layouts.admin-app')
@section('title', 'Price Match Review')
@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Price Match Review</h1>
            <p class="text-sm text-gray-500">Scraped products proposed as matches for catalog variants.</p>
        </div>
        <a href="{{ route('admin.catalog.research.pricing.index') }}" class="text-sm text-gray-500 hover:underline">← Back to pricing</a>
    </div>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>@endif

    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Confirming a match creates a real price on that product. A wrong link puts the wrong price on the
        wrong item — check the two sides match before confirming.
    </div>

    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2">
        @foreach(\App\Enums\Catalog\Pricing\MatchStatusEnum::cases() as $st)
            @php $active = request('status', 'pending') === $st->value; @endphp
            <a href="{{ route('admin.catalog.research.pricing.matches', ['status' => $st->value]) }}"
               class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $active ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                {{ $st->label() }}
                <span class="ms-1 opacity-70">{{ $counts[$st->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse($matches as $m)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $m->status?->badgeClass() }}">{{ $m->status?->label() }}</span>
                        <span class="text-xs text-gray-500">{{ $m->match_method?->label() }}</span>
                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ (float) $m->confidence_score }}%</span>
                    </div>
                    @if($m->status === \App\Enums\Catalog\Pricing\MatchStatusEnum::Pending)
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.catalog.research.pricing.matches.confirm', $m->id) }}"
                                  onsubmit="return confirm('Confirm this match and create the price?')">
                                @csrf
                                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Confirm</button>
                            </form>
                            <form method="POST" action="{{ route('admin.catalog.research.pricing.matches.reject', $m->id) }}">
                                @csrf
                                <button class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">Reject</button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Both sides, so a mismatch is obvious at a glance --}}
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Scraped product</div>
                        <div class="mt-1 text-sm text-gray-800">{{ Str::limit($m->scraped_name, 90) ?: '—' }}</div>
                        <div class="mt-1 font-mono text-xs text-gray-500">{{ $m->scraped_sku ?: 'no SKU' }}</div>
                        <div class="mt-1 text-sm font-semibold text-gray-800">
                            {{ $m->scraped_price ? number_format((float) $m->scraped_price, 2).' '.($m->scraped_currency ?: 'SAR') : 'no price' }}
                        </div>
                        @if($m->scraped_url)
                            <a href="{{ $m->scraped_url }}" target="_blank" rel="noopener" class="mt-1 inline-block text-xs text-emerald-600 hover:underline">view page ↗</a>
                        @endif
                    </div>

                    <div class="rounded-lg bg-emerald-50 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Catalog variant</div>
                        <div class="mt-1 text-sm text-gray-800">{{ Str::limit($m->variant->variant_name ?? '—', 90) }}</div>
                        <div class="mt-1 font-mono text-xs text-gray-500">{{ $m->variant->manufacturer_sku ?? 'no SKU' }}</div>
                        <div class="mt-1 text-xs text-gray-600">{{ $m->variant->manufacturer->name ?? '—' }}</div>
                        @if($m->variant)
                            <a href="{{ route('admin.catalog.research.pricing.show', $m->variant->uuid) }}" class="mt-1 inline-block text-xs text-emerald-600 hover:underline">open product →</a>
                        @endif
                    </div>
                </div>

                @if($m->match_reasons)
                    <div class="mt-2 text-xs text-gray-500">
                        Matched on: {{ collect($m->match_reasons)->map(fn($v,$k)=>"$k=$v")->implode(', ') }}
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-10 text-center text-gray-500 shadow-sm">
                No matches with this status.
                <p class="mt-1 text-xs">Run <span class="font-mono">php artisan catalog:match-prices</span> to look for more.</p>
            </div>
        @endforelse
    </div>

    {{ $matches->links() }}
</div>
@endsection
