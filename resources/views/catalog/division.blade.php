@extends('layouts.app')

@section('title', $division . ' — Qimta Catalog')

@section('styles')
<style>
    /* ── BREADCRUMB ── */
    .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #999; margin-bottom: 32px; flex-wrap: wrap; }
    .breadcrumb a { color: #999; transition: color .2s; }
    .breadcrumb a:hover { color: var(--green); }
    .breadcrumb svg { width: 12px; height: 12px; stroke: #bbb; fill: none; stroke-width: 2; flex-shrink: 0; }

    /* ── PAGE HEADER ── */
    .div-header { padding: 40px 0 32px; }
    .div-header h1 { font-size: clamp(24px, 3.5vw, 36px); font-weight: 800; letter-spacing: -0.8px; line-height: 1.2; margin-bottom: 10px; }
    .div-header p { font-size: 14px; color: #666; max-width: 640px; line-height: 1.65; }

    /* ── STATS ROW ── */
    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 36px; }
    @media (max-width: 700px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
    .stat-card { border: 1.5px solid var(--border); border-radius: 12px; padding: 20px; background: var(--white); }
    .stat-card .label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 8px; }
    .stat-card .value { font-size: 26px; font-weight: 800; letter-spacing: -1px; color: var(--dark); }
    .stat-card .value.green { color: var(--green); }
    .stat-card .sub { font-size: 12px; color: #888; margin-top: 4px; }

    /* ── LAYOUT ── */
    .catalog-layout { display: grid; grid-template-columns: 220px 1fr; gap: 32px; padding-bottom: 64px; align-items: start; }
    @media (max-width: 820px) { .catalog-layout { grid-template-columns: 1fr; } }

    /* ── SIDEBAR ── */
    .filter-sidebar { border: 1.5px solid var(--border); border-radius: 14px; padding: 24px; background: var(--white); position: sticky; top: 88px; }
    .filter-sidebar h6 { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 12px; margin-top: 20px; }
    .filter-sidebar h6:first-child { margin-top: 0; }
    .filter-sidebar select { width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13px; font-family: inherit; color: var(--dark); background: var(--white); outline: none; cursor: pointer; transition: border-color .2s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }
    .filter-sidebar select:focus { border-color: var(--green); }
    .filter-sidebar .filter-checks { display: flex; flex-direction: column; gap: 8px; }
    .filter-checks label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #444; cursor: pointer; }
    .filter-checks input[type=checkbox] { accent-color: var(--green); width: 15px; height: 15px; flex-shrink: 0; }
    .filter-apply { width: 100%; margin-top: 20px; padding: 10px; background: var(--green); color: var(--white); border: none; border-radius: 8px; font-size: 13px; font-weight: 700; font-family: inherit; cursor: pointer; transition: background .2s; }
    .filter-apply:hover { background: #005a32; }
    .filter-reset { display: block; text-align: center; font-size: 12px; color: #888; margin-top: 8px; cursor: pointer; transition: color .2s; text-decoration: none; }
    .filter-reset:hover { color: var(--green); }
    .compliance-note { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-top: 20px; font-size: 12px; color: #166534; display: flex; align-items: flex-start; gap: 8px; }
    .compliance-note svg { width: 14px; height: 14px; flex-shrink: 0; stroke: #16a34a; fill: none; stroke-width: 2; margin-top: 1px; }

    /* ── ITEMS GRID ── */
    .items-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    @media (max-width: 640px) { .items-grid { grid-template-columns: 1fr; } }

    /* ── ITEM CARD ── */
    .item-card { border: 1.5px solid var(--border); border-radius: 14px; padding: 24px; background: var(--white); display: flex; flex-direction: column; transition: border-color .2s, box-shadow .2s; }
    .item-card:hover { border-color: var(--green); box-shadow: 0 4px 20px rgba(0,106,59,0.08); }
    .item-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
    .item-card h3 { font-size: 15px; font-weight: 700; letter-spacing: -0.2px; line-height: 1.3; color: var(--dark); }
    .item-badge { flex-shrink: 0; background: #f3f4f6; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; color: #555; white-space: nowrap; }
    .item-materials-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #aaa; margin-bottom: 5px; margin-top: 12px; }
    .item-materials-val { font-size: 12.5px; color: #555; line-height: 1.5; }
    .item-view-btn { margin-top: 16px; display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid var(--green); color: var(--green); border-radius: 8px; padding: 9px 14px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; transition: background .2s, color .2s; cursor: pointer; text-decoration: none; }
    .item-view-btn:hover { background: var(--green); color: var(--white); }

    /* ── SEARCH BAR ── */
    .items-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    .items-search-wrap { position: relative; flex: 1; max-width: 320px; }
    .items-search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: #aaa; fill: none; stroke-width: 2; }
    .items-search { width: 100%; padding: 10px 12px 10px 36px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13px; font-family: inherit; outline: none; transition: border-color .2s; }
    .items-search:focus { border-color: var(--green); }
    .items-count { font-size: 13px; color: #888; }
    .items-count strong { color: var(--dark); }

    /* ── PAGINATION ── */
    .pagination-wrap { display: flex; justify-content: center; gap: 6px; margin-top: 32px; flex-wrap: wrap; }
    .pagination-wrap a, .pagination-wrap span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13px; font-weight: 600; color: #555; transition: all .2s; text-decoration: none; }
    .pagination-wrap a:hover { border-color: var(--green); color: var(--green); }
    .pagination-wrap span.active { background: var(--green); border-color: var(--green); color: var(--white); }
    .pagination-wrap span.disabled { color: #ccc; cursor: default; }

    /* ── EMPTY ── */
    .items-empty { grid-column: 1/-1; text-align: center; padding: 60px 24px; color: #999; }
    .items-empty svg { width: 40px; height: 40px; stroke: #ddd; fill: none; stroke-width: 1.5; margin: 0 auto 12px; }
</style>
@endsection

@section('content')
<div class="container">

    {{-- Breadcrumb --}}
    <div style="padding-top:32px;">
        <div class="breadcrumb">
            <a href="/">Home</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="{{ route('catalog.index') }}">Catalog</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span>{{ $division }}</span>
        </div>
    </div>

    {{-- Header --}}
    <div class="div-header">
        <h1>{{ $division }} — All Item Descriptions</h1>
        <p>Browse {{ strtolower($division) }} item families, technical specifications, standards, materials, and indexed product coverage inside Qimta's construction catalog.</p>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="label">System Capacity</div>
            <div class="value">{{ number_format($stats['products']) }}</div>
            <div class="sub">Products in {{ Illuminate\Support\Str::words($division, 2, '') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Item Definitions</div>
            <div class="value green">{{ number_format($stats['items']) }}</div>
            <div class="sub">Active Item Descriptions</div>
        </div>
        <div class="stat-card">
            <div class="label">Pricing Layers</div>
            <div class="value green" style="font-size:20px; letter-spacing:-0.5px;">STD/ENG</div>
            <div class="sub">Standard &amp; Engineered</div>
        </div>
        <div class="stat-card">
            <div class="label">Est. Lead Time</div>
            <div class="value green" style="font-size:20px; letter-spacing:-0.5px;">PROJECT</div>
            <div class="sub">Based on BOQ Scale</div>
        </div>
    </div>

    {{-- Layout: sidebar + grid --}}
    <div class="catalog-layout">

        {{-- Sidebar Filters --}}
        <aside class="filter-sidebar">
            <form method="GET" action="{{ route('catalog.division', $slug) }}">
                <h6>Filter Selection</h6>

                @if($materials->isNotEmpty())
                <h6>Material</h6>
                <select name="material">
                    <option value="">— All Materials —</option>
                    @foreach($materials as $m)
                        <option value="{{ $m }}" {{ request('material') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                @endif

                @if($sizes->isNotEmpty())
                <h6>Size Range</h6>
                <select name="size">
                    <option value="">— All Sizes —</option>
                    @foreach($sizes as $s)
                        <option value="{{ $s }}" {{ request('size') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                @endif

                @if($leadTimes->isNotEmpty())
                <h6>Pricing Layer</h6>
                <select name="lead_time">
                    <option value="">— All —</option>
                    @foreach($leadTimes as $lt)
                        <option value="{{ $lt }}" {{ request('lead_time') == $lt ? 'selected' : '' }}>{{ $lt }}</option>
                    @endforeach
                </select>
                @endif

                <button type="submit" class="filter-apply">Apply Filters</button>
                @if(request()->hasAny(['material','size','lead_time','q']))
                    <a href="{{ route('catalog.division', $slug) }}" class="filter-reset">Clear all filters</a>
                @endif
            </form>

            <div class="compliance-note">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Compliance verification is available for all technical items in this catalog.</span>
            </div>
        </aside>

        {{-- Items --}}
        <div>
            <div class="items-topbar">
                <form method="GET" action="{{ route('catalog.division', $slug) }}" style="display:contents;">
                    @foreach(request()->except('q') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <div class="items-search-wrap">
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="items-search" type="text" name="q" value="{{ request('q') }}" placeholder="Search items...">
                    </div>
                </form>
                <div class="items-count">
                    Showing <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong> of <strong>{{ number_format($items->total()) }}</strong> items
                </div>
            </div>

            <div class="items-grid">
                @forelse($items as $item)
                <div class="item-card">
                    <div class="item-card-head">
                        <h3>{{ $item->item_description }}</h3>
                        <span class="item-badge">{{ number_format($item->products) }} PRODUCTS</span>
                    </div>
                    @if($item->common_materials)
                        <div class="item-materials-label">Common Materials</div>
                        <div class="item-materials-val">{{ Str::limit($item->common_materials, 80) }}</div>
                    @endif
                    <a href="{{ route('catalog.index') }}?item={{ urlencode($item->item_description) }}" class="item-view-btn">
                        View Specifications
                    </a>
                </div>
                @empty
                <div class="items-empty">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <p>No items match your filters. <a href="{{ route('catalog.division', $slug) }}" style="color:var(--green);">Clear filters</a></p>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($items->hasPages())
            <div class="pagination-wrap">
                @if($items->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $items->previousPageUrl() }}">&laquo;</a>
                @endif

                @foreach($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
