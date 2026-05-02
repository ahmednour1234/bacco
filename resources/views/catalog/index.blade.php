@extends('layouts.app')

@section('title', 'Browse the Qimta Construction Catalog')

@section('styles')
<style>
    /* ── BREADCRUMB ── */
    .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #999; margin-bottom: 32px; }
    .breadcrumb a { color: #999; transition: color .2s; }
    .breadcrumb a:hover { color: var(--green); }
    .breadcrumb svg { width: 12px; height: 12px; stroke: #bbb; fill: none; stroke-width: 2; flex-shrink: 0; }

    /* ── PAGE HEADER ── */
    .catalog-header { padding: 48px 0 40px; }
    .catalog-header h1 { font-size: clamp(28px, 4vw, 42px); font-weight: 800; letter-spacing: -1px; line-height: 1.15; margin-bottom: 12px; }
    .catalog-header .subtitle { font-size: 15px; color: #555; max-width: 640px; line-height: 1.6; margin-bottom: 32px; }
    .catalog-header .subtitle span { color: var(--green); font-weight: 700; }

    /* ── SEARCH ── */
    .catalog-search-wrap { position: relative; max-width: 480px; margin-bottom: 36px; }
    .catalog-search-wrap svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: #aaa; fill: none; stroke-width: 2; }
    .catalog-search { width: 100%; padding: 13px 16px 13px 44px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; font-family: inherit; outline: none; transition: border-color .2s, box-shadow .2s; background: var(--white); }
    .catalog-search:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(0,106,59,.1); }

    /* ── INFO BANNER ── */
    .catalog-info { display: flex; gap: 24px; flex-wrap: wrap; background: #f9f9f9; border: 1px solid var(--border); border-radius: 12px; padding: 20px 24px; margin-bottom: 40px; font-size: 13px; color: #555; line-height: 1.6; }
    .catalog-info-col { flex: 1; min-width: 260px; }
    .catalog-info-col h6 { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #999; margin-bottom: 6px; }
    .catalog-info-col p { color: #555; font-size: 13px; }
    .catalog-info-col p a { color: var(--green); font-weight: 600; }
    .catalog-info-note { display: flex; align-items: flex-start; gap: 8px; flex: 1; min-width: 260px; }
    .catalog-info-note svg { width: 16px; height: 16px; flex-shrink: 0; color: #e5900e; margin-top: 2px; }

    /* ── DIVISION GRID ── */
    .division-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding-bottom: 64px; }
    @media (max-width: 900px) { .division-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 560px) { .division-grid { grid-template-columns: 1fr; } }

    /* ── DIVISION CARD ── */
    .div-card { border: 1.5px solid var(--border); border-radius: 14px; padding: 24px; background: var(--white); transition: border-color .2s, box-shadow .2s, transform .2s; display: flex; flex-direction: column; }
    .div-card:hover { border-color: var(--green); box-shadow: 0 4px 24px rgba(0,106,59,0.08); transform: translateY(-2px); }
    .div-card-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 6px; }
    .div-card-num { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #bbb; }
    .div-card h3 { font-size: 16px; font-weight: 700; letter-spacing: -0.3px; margin-bottom: 4px; line-height: 1.3; }
    .div-card .div-name-ar { font-size: 12px; color: #999; margin-bottom: 18px; }
    .div-stats { display: flex; gap: 20px; border-top: 1px solid var(--border); padding-top: 16px; margin-top: auto; }
    .div-stat { display: flex; flex-direction: column; gap: 2px; }
    .div-stat strong { font-size: 16px; font-weight: 800; color: var(--dark); letter-spacing: -0.5px; }
    .div-stat span { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; }
    .div-browse { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: var(--green); margin-top: 18px; transition: gap .2s; }
    .div-browse svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2.5; transition: transform .2s; }
    .div-card:hover .div-browse { gap: 10px; }
    .div-card:hover .div-browse svg { transform: translateX(3px); }

    /* ── EMPTY STATE ── */
    .catalog-empty { text-align: center; padding: 80px 24px; color: #999; }
    .catalog-empty svg { width: 48px; height: 48px; stroke: #ccc; fill: none; stroke-width: 1.5; margin: 0 auto 16px; }
    .catalog-empty h3 { font-size: 18px; font-weight: 700; color: #555; margin-bottom: 8px; }
</style>
@endsection

@section('content')
<div class="container">

    {{-- Breadcrumb --}}
    <div style="padding-top:32px;">
        <div class="breadcrumb">
            <a href="/">HOME</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span>CATALOG</span>
        </div>
    </div>

    {{-- Header --}}
    <div class="catalog-header">
        <h1>Browse the Qimta Construction Catalog</h1>
        <p class="subtitle">
            Access the industry's most rigorous data set. Explore
            <span>{{ number_format($totals['divisions']) }} divisions</span>,
            <span>{{ number_format($totals['categories']) }} categories</span>,
            <span>{{ number_format($totals['items']) }} items</span>, and
            <span>{{ number_format($totals['products']) }} verified products</span>.
        </p>

        {{-- Search --}}
        <div class="catalog-search-wrap">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="catalog-search" id="divSearch" type="text" placeholder="Search by division or keyword..." autocomplete="off">
        </div>

        {{-- Info Banner --}}
        <div class="catalog-info">
            <div class="catalog-info-col">
                <h6>Public catalog structure</h6>
                <p>Our technical hierarchy follows international construction standards. Navigate through Division Hubs to Category Hubs and finally to specific Item&nbsp;Description Pages for technical specifications.</p>
            </div>
            <div class="catalog-info-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p><strong>Note:</strong> Live pricing data and specific manufacturer SKUs are restricted to registered enterprise accounts. <a href="{{ route('enduser.register') }}">Sign up to access verified rates.</a></p>
            </div>
        </div>
    </div>

    {{-- Division Grid --}}
    @if($rows->isEmpty())
        <div class="catalog-empty">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <h3>No catalog data yet</h3>
            <p>Import your first catalog file to get started.</p>
        </div>
    @else
        <div class="division-grid" id="divGrid">
            @foreach($rows as $d)
            <a href="{{ route('catalog.division', $d->slug) }}" class="div-card" data-name="{{ strtolower($d->division) }}">
                <div class="div-card-top">
                    <span class="div-card-num">DIV-{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h3>{{ $d->division }}</h3>
                <div class="div-stats">
                    <div class="div-stat">
                        <strong>{{ number_format($d->products) }}</strong>
                        <span>Products</span>
                    </div>
                    <div class="div-stat">
                        <strong>{{ number_format($d->items) }}</strong>
                        <span>Items</span>
                    </div>
                    <div class="div-stat">
                        <strong>{{ number_format($d->cats) }}</strong>
                        <span>Cats</span>
                    </div>
                </div>
                <div class="div-browse">
                    Browse Division
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </div>
            </a>
            @endforeach
        </div>
    @endif

</div>

<script>
document.getElementById('divSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#divGrid .div-card').forEach(card => {
        card.style.display = (!q || card.dataset.name.includes(q)) ? '' : 'none';
    });
});
</script>
@endsection
