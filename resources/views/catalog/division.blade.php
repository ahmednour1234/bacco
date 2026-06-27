@extends('layouts.app')

@section('title', app()->getLocale() === 'ar'
    ? $division . ' — ' . number_format($stats['products'] ?? 0) . ' منتج · تسعير BOQ السعودية | كيمتا'
    : $division . ' — ' . number_format($stats['products'] ?? 0) . ' Products · BOQ Pricing KSA | Qimta')

@section('description', app()->getLocale() === 'ar'
    ? 'تصفّح ' . number_format($stats['products'] ?? 0) . ' منتج معتمد لفئة ' . $division . '. تسعير BOQ في أقل من 60 ثانية. مجاني للمقاولين في السعودية والخليج.'
    : 'Browse ' . number_format($stats['products'] ?? 0) . ' verified ' . strtolower($division) . ' products. BOQ pricing in under 60 seconds. Free for contractors in Saudi Arabia and GCC.')

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

    /* ── MOBILE FILTER TOGGLE ── */
    .filter-toggle-btn { display: none; width: 100%; margin-bottom: 12px; padding: 11px 16px; background: var(--white); border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; font-weight: 700; font-family: inherit; color: var(--dark); cursor: pointer; text-align: left; align-items: center; justify-content: space-between; gap: 8px; }
    .filter-toggle-btn svg { width: 16px; height: 16px; stroke: var(--green); fill: none; stroke-width: 2; flex-shrink: 0; transition: transform .25s; }
    .filter-toggle-btn svg.open { transform: rotate(180deg); }
    @media (max-width: 820px) { .filter-toggle-btn { display: flex; } }

    /* ── SIDEBAR ── */
    .filter-sidebar { border: 1.5px solid var(--border); border-radius: 14px; padding: 24px; background: var(--white); }
    .filter-sidebar h2 { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 12px; margin-top: 20px; }
    .filter-sidebar h2:first-child { margin-top: 0; }
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

    /* ── CAT CARD HEADER (mockup CAT-XX) ── */
    .cat-card { background: #0d3d24; color: #fff; border-radius: 12px; padding: 22px 26px; margin: 28px 0 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .cat-card .cat-title { font-size: clamp(18px, 2.4vw, 22px); font-weight: 800; letter-spacing: -0.3px; }
    .cat-card .cat-sub { margin-top: 8px; display: inline-flex; gap: 10px; align-items: center; background: rgba(255,255,255,.12); border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600; }
    .cat-card .cat-sub .dot { opacity: .6; }
    .cat-card .cat-code { font-size: 11px; font-weight: 700; letter-spacing: 2px; color: #8fd3ae; }
    .cat-url { font-size: 12px; color: #888; padding: 12px 4px 0; border-bottom: 1px solid var(--border); margin-bottom: 4px; word-break: break-all; }

    /* ── SEO META PREVIEW ── */
    .seo-block { margin-top: 18px; }
    .seo-block .seo-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 8px; }
    .seo-box { border: 1.5px solid var(--border); border-radius: 10px; padding: 14px 16px; background: #f7faf8; font-size: 13.5px; line-height: 1.6; color: var(--dark); }
    .seo-box.desc { color: #444; }
    .seo-count { font-size: 11px; color: #999; margin-top: 6px; }

    /* ── ITEM FAMILY CARDS (mockup grid) ── */
    .fam-section-title { display: flex; align-items: center; gap: 10px; font-size: 17px; font-weight: 800; letter-spacing: -0.3px; color: var(--dark); margin: 40px 0 6px; }
    .fam-section-title::before { content: ""; width: 4px; height: 20px; background: var(--green); border-radius: 3px; }
    .fam-section-sub { font-size: 13px; color: #777; margin-bottom: 20px; }
    .fam-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    @media (max-width: 640px) { .fam-grid { grid-template-columns: 1fr; } }
    .fam-card { border: 1.5px solid var(--border); border-radius: 12px; padding: 18px 20px; background: var(--white); transition: border-color .2s, box-shadow .2s; text-decoration: none; color: inherit; display: block; }
    .fam-card:hover { border-color: var(--green); box-shadow: 0 4px 16px rgba(0,106,59,.08); }
    .fam-card .fam-en { font-size: 11px; font-weight: 700; letter-spacing: .5px; color: #aaa; text-transform: uppercase; margin-bottom: 6px; }
    .fam-card .fam-ar { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .fam-card .fam-meta { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--green); font-weight: 600; }
    .fam-card .fam-meta .sep { color: #ccc; }

    /* ── MATERIALS ── */
    .mat-section-title { display: flex; align-items: center; gap: 10px; font-size: 17px; font-weight: 800; letter-spacing: -0.3px; color: var(--dark); margin: 40px 0 14px; }
    .mat-section-title::before { content: ""; width: 4px; height: 20px; background: var(--green); border-radius: 3px; }
    .mat-list { font-size: 13.5px; color: #555; line-height: 1.9; }
    .mat-list .mat-pill { display: inline-block; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 16px; padding: 3px 12px; margin: 0 4px 6px 0; font-size: 12.5px; font-weight: 600; }

    /* ── GENERIC SEO SECTION (pricing layers / how-to / standards) ── */
    .seo-section-title { display: flex; align-items: center; gap: 10px; font-size: 17px; font-weight: 800; letter-spacing: -0.3px; color: var(--dark); margin: 40px 0 12px; }
    .seo-section-title::before { content: ""; width: 4px; height: 20px; background: var(--green); border-radius: 3px; }
    .seo-section-body { font-size: 13.5px; color: #555; line-height: 1.85; }
    .seo-section-body strong { color: var(--dark); }

    /* ── RELATED CATEGORIES ── */
    .relcat-list { font-size: 13.5px; color: #555; line-height: 1.9; }
    .relcat-list a { color: var(--green); font-weight: 600; }
    .relcat-list a:hover { text-decoration: underline; }
    .relcat-list .sep { color: #ccc; margin: 0 4px; }

    /* ── FAQ ── */
    .faq-item { border-bottom: 1px solid var(--border); padding: 16px 0; }
    .faq-item:last-child { border-bottom: none; }
    .faq-q { font-size: 14.5px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .faq-a { font-size: 13.5px; color: #666; line-height: 1.75; }

    /* ── SCHEMA BADGE ── */
    .schema-badge { margin-top: 36px; background: #f5f3fb; border: 1px solid #ddd6f3; border-left: 3px solid #7c3aed; border-radius: 8px; padding: 12px 16px; font-size: 12.5px; color: #5b21b6; font-family: ui-monospace, monospace; }
</style>
@endsection

@section('content')
@php
$_breadcrumb = json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>'https://www.qimta.com/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Construction Catalog','item'=>'https://www.qimta.com/catalog'],
        ['@type'=>'ListItem','position'=>3,'name'=>$division,'item'=>'https://www.qimta.com' . request()->getPathInfo()],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$_itemListSchema = json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => $division,
    'description'     => 'Construction products in the ' . $division . ' category — priced via Qimta RAG engine.',
    'url'             => 'https://www.qimta.com' . request()->getPathInfo(),
    'numberOfItems'   => $stats['products'] ?? 0,
    'itemListElement' => $items->map(fn($item, $i) => [
        '@type'    => 'ListItem',
        'position' => $items->firstItem() + $i,
        'name'     => $item->item_description,
    ])->values()->toArray(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_breadcrumb !!}</script>
<script type="application/ld+json">{!! $_itemListSchema !!}</script>
<div class="container">

    {{-- Breadcrumb --}}
    <div style="padding-top:32px;">
        <nav aria-label="breadcrumb" class="breadcrumb">
            <a href="/">Home</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="{{ route('catalog.index') }}">Catalog</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span aria-current="page">{{ $division }}</span>
        </nav>
    </div>

    @php
        $__isAr     = app()->getLocale() === 'ar';
        $__cat      = $category ?? null;
        $__catCode  = $__cat ? 'CAT-' . str_pad($__cat->id, 2, '0', STR_PAD_LEFT) : null;
        $__prodN    = number_format($stats['products'] ?? 0);
        $__itemN    = number_format($stats['items'] ?? 0);
        $__catUrl   = 'qimta.com' . request()->getPathInfo();

        // Generated SEO meta (mirrors layout's <title>/<meta description>)
        $__metaTitle = $__isAr
            ? $division . ' — ' . $__prodN . ' منتج · تسعير BOQ السعودية | كيمتا'
            : $division . ' — ' . $__prodN . ' Products · BOQ Pricing KSA | Qimta';
        $__metaDesc  = $__isAr
            ? 'تصفّح ' . $__prodN . ' منتج معتمد لفئة ' . $division . '. تسعير BOQ في أقل من 60 ثانية. مجاني للمقاولين في السعودية والخليج.'
            : 'Browse ' . $__prodN . ' verified ' . strtolower($division) . ' products. BOQ pricing in under 60 seconds. Free for contractors in Saudi Arabia and GCC.';
    @endphp

    {{-- CAT card header (mockup) --}}
    @if($__cat)
    <div class="cat-card">
        <div>
            <div class="cat-title">{{ $division }}</div>
            <div class="cat-sub">
                <span>{{ $__prodN }} {{ $__isAr ? 'منتج' : 'products' }}</span>
                <span class="dot">·</span>
                <span>{{ $__itemN }} {{ $__isAr ? 'عائلة' : 'families' }}</span>
            </div>
        </div>
        <div class="cat-code">{{ $__catCode }}</div>
    </div>
    <div class="cat-url">{{ $__catUrl }}</div>

    {{-- SEO meta previews --}}
    <div class="seo-block">
        <div class="seo-label">Meta Title</div>
        <div class="seo-box">{{ $__metaTitle }}</div>
        <div class="seo-count">{{ mb_strlen($__metaTitle) }} {{ $__isAr ? 'حرفاً' : 'chars' }}</div>
    </div>
    <div class="seo-block">
        <div class="seo-label">Meta Description</div>
        <div class="seo-box desc">{{ $__metaDesc }}</div>
        <div class="seo-count">{{ mb_strlen($__metaDesc) }} {{ $__isAr ? 'حرفاً' : 'chars' }}</div>
    </div>
    @endif

    {{-- GEO Fact Block — machine-readable citation paragraph for LLMs/AI overviews --}}
    @php
        $__factSlug = request()->getPathInfo();
        $__factUrl  = 'qimta.com' . $__factSlug;
        $__factItems = number_format($stats['items']);
        $__factProducts = number_format($stats['products']);
    @endphp
    <p id="fact-block" style="font-size:13px;color:#666;line-height:1.75;border-left:3px solid var(--green);padding:12px 16px;background:#f9fdf9;border-radius:0 8px 8px 0;margin-bottom:28px;">
        Qimta indexes {{ $__factProducts }} verified {{ strtolower($division) }} products across {{ $__factItems }} item families.
        All products are priced via RAG retrieval from manufacturer databases.
        Delivery lead times range from 2 to 8 weeks depending on specification.
        Pricing is free for construction buyers and procurement teams across Saudi Arabia and GCC.
        Accessible at: {{ $__factUrl }}
    </p>

    {{-- Header --}}
    <div class="div-header">
        <h1>{{ $division }} — All Item Descriptions</h1>
        <p>Browse {{ strtolower($division) }} item families, technical specifications, standards, materials, and indexed product coverage inside Qimta's construction catalog.</p>
    </div>

    {{-- Item families available for pricing (mockup grid) --}}
    @if($items->isNotEmpty())
    <div class="fam-section-title">{{ $__isAr ? 'عائلات البنود المتاحة للتسعير' : 'Item Families Available for Pricing' }}</div>
    <p class="fam-section-sub">{{ $__isAr ? 'تتضمن هذه الفئة بنوداً للعزل الصوتي بمواصفات ومواد محددة، مُتقاطعة مع قواعد بيانات المصنّعين.' : 'These families cross-reference manufacturer databases with verified specs and materials.' }}</p>
    <div class="fam-grid">
        @foreach($items as $fam)
            @php
                $__famKey   = 'catalog.items.' . $fam->item_description;
                $__famTitle = ($__isAr && Lang::has($__famKey)) ? __($__famKey) : $fam->item_description;
            @endphp
            <a href="{{ route('catalog.item', [$slug, Str::slug($fam->item_description)]) }}" class="fam-card">
                <div class="fam-en">{{ $fam->item_description }}</div>
                <div class="fam-ar">{{ $__famTitle }}</div>
                <div class="fam-meta">
                    @if($fam->common_materials)<span>{{ Str::limit($fam->common_materials, 30) }}</span><span class="sep">·</span>@endif
                    <span>{{ number_format($fam->products) }} {{ $__isAr ? 'منتجات' : 'products' }}</span>
                </div>
            </a>
        @endforeach
    </div>
    @endif

    {{-- Available materials --}}
    @if($materials->isNotEmpty())
    <div class="mat-section-title">{{ $__isAr ? 'المواد المتوفرة' : 'Available Materials' }}</div>
    <div class="mat-list">
        @foreach($materials as $m)
            <span class="mat-pill">{{ $m }}</span>
        @endforeach
    </div>
    @endif

    {{-- Pricing layers — Standard & Engineered --}}
    <div class="seo-section-title">{{ $__isAr ? 'طبقات التسعير — Standard وEngineered' : 'Pricing Layers — Standard & Engineered' }}</div>
    <div class="seo-section-body">
        {{ $__isAr
            ? 'Standard (STD): 2-6 أسابيع · Engineered (ENG): حتى 12-18 أسبوعاً للمواصفات المُخصّصة. كلاهما يُسعَّر عبر RAG مجاناً.'
            : 'Standard (STD): 2-6 weeks · Engineered (ENG): up to 12-18 weeks for custom specs. Both priced via the RAG engine for free.' }}
    </div>

    {{-- How pricing works with Qimta --}}
    <div class="seo-section-title">{{ $__isAr ? 'كيف تُسعّر ' . $division . ' مع كيمتا' : 'How to Price ' . $division . ' with Qimta' }}</div>
    <div class="seo-section-body">
        {{ $__isAr
            ? 'ارفع جدول الكميات بصيغة Excel أو PDF أو CSV. يُطابق محرك RAG بنود ' . $division . ' تلقائياً مع الـ ' . $__prodN . ' منتج ويُخرج تسعيراً مكتملاً في أقل من 60 ثانية.'
            : 'Upload your BOQ as Excel, PDF, or CSV. The RAG engine auto-matches ' . strtolower($division) . ' line items against ' . $__prodN . ' products and returns a complete quote in under 60 seconds.' }}
    </div>

    {{-- Related categories --}}
    @if(($relatedCategories ?? collect())->isNotEmpty())
    <div class="seo-section-title">{{ $__isAr ? 'الفئات المرتبطة' : 'Related Categories' }}</div>
    <div class="relcat-list">
        @foreach($relatedCategories as $i => $rc)
            <a href="{{ route('catalog.category', $rc->slug) }}">{{ $rc->name }}</a>@if(!$loop->last)<span class="sep">·</span>@endif
        @endforeach
    </div>
    @endif

    {{-- Accreditation & standards --}}
    <div class="seo-section-title">{{ $__isAr ? 'معايير الاعتماد — SASO والمعايير الدولية' : 'Accreditation — SASO & International Standards' }}</div>
    <div class="seo-section-body">
        {{ $__isAr
            ? 'توضّح منتجات ' . $division . ' معايير SASO والمعايير الدولية ISO وASTM لتسهيل القبول في مشاريع المباني السعودية.'
            : $division . ' products document SASO, ISO, and ASTM standards to ease approval on Saudi building projects.' }}
    </div>

    {{-- FAQ --}}
    @php
        $__faqs = $__isAr ? [
            ['q' => 'كم عدد منتجات ' . $division . ' المتوفرة في كيمتا؟', 'a' => 'يضم كتالوج كيمتا ' . $__prodN . ' منتجاً عبر ' . $__itemN . ' عائلة بنود. تسعيرها مجاني للمشترين في السعودية والخليج.'],
            ['q' => 'ما سعر ' . $division . ' في السعودية؟', 'a' => 'تتفاوت الأسعار حسب المواصفات والمواد والكميات. ارفع جدول الكميات للتسعير الفوري في أقل من 60 ثانية — مجاناً.'],
            ['q' => 'ما وقت التسليم؟', 'a' => 'من 2 إلى 8 أسابيع حسب نوع المواصفات وحجم المشروع.'],
            ['q' => 'هل المواد مناسبة للمناخ السعودي الحار؟', 'a' => 'نعم. المنتجات توضّح معايير مقاومة الحرارة والأشعة فوق البنفسجية وفق المعايير المعتمدة.'],
        ] : [
            ['q' => 'How many ' . strtolower($division) . ' products are available in Qimta?', 'a' => 'The Qimta catalog includes ' . $__prodN . ' products across ' . $__itemN . ' item families. Pricing is free for buyers in Saudi Arabia and the GCC.'],
            ['q' => 'What is the price of ' . strtolower($division) . ' in Saudi Arabia?', 'a' => 'Prices vary by spec, material, and quantity. Upload your BOQ for an instant quote in under 60 seconds — free.'],
            ['q' => 'What is the delivery time?', 'a' => '2 to 8 weeks depending on spec type and project scale.'],
            ['q' => 'Are the materials suitable for the hot Saudi climate?', 'a' => 'Yes. Products document heat- and UV-resistance ratings against approved standards.'],
        ];
    @endphp
    <div class="seo-section-title">{{ $__isAr ? 'الأسئلة الشائعة' : 'Frequently Asked Questions' }}</div>
    <div>
        @foreach($__faqs as $faq)
        <div class="faq-item">
            <div class="faq-q">{{ $faq['q'] }}</div>
            <div class="faq-a">{{ $faq['a'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- FAQPage JSON-LD --}}
    @php
        $__faqSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type'    => 'FAQPage',
            'mainEntity' => collect($__faqs)->map(fn($f) => [
                '@type' => 'Question',
                'name'  => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ])->toArray(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $__faqSchema !!}</script>

    {{-- Schema badge --}}
    <div class="schema-badge">Schema: BreadcrumbList + FAQPage + ItemList ({{ $items->total() }} items)</div>

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
        <aside class="filter-sidebar" x-data="{ open: window.innerWidth > 820 }">
            <button type="button" class="filter-toggle-btn" @click="open = !open" aria-expanded="open" aria-controls="filter-body">
                <span>Filter Selection</span>
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'open' : ''"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div id="filter-body" x-show="open" x-collapse.duration.200ms>
            <form method="GET" action="{{ route($filterRoute, $slug) }}">
                <h2>Filter Selection</h2>

                @if($materials->isNotEmpty())
                <h2>Material</h2>
                <select name="material">
                    <option value="">— All Materials —</option>
                    @foreach($materials as $m)
                        <option value="{{ $m }}" {{ request('material') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                @endif

                @if($sizes->isNotEmpty())
                <h2>Size Range</h2>
                <select name="size">
                    <option value="">— All Sizes —</option>
                    @foreach($sizes as $s)
                        <option value="{{ $s }}" {{ request('size') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                @endif

                @if($leadTimes->isNotEmpty())
                <h2>Pricing Layer</h2>
                <select name="lead_time">
                    <option value="">— All —</option>
                    @foreach($leadTimes as $lt)
                        <option value="{{ $lt }}" {{ request('lead_time') == $lt ? 'selected' : '' }}>{{ $lt }}</option>
                    @endforeach
                </select>
                @endif

                <button type="submit" class="filter-apply">Apply Filters</button>
                @if(request()->hasAny(['material','size','lead_time','q']))
                    <a href="{{ route($filterRoute, $slug) }}" class="filter-reset">Clear all filters</a>
                @endif
            </form>

            <div class="compliance-note">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Compliance verification is available for all technical items in this catalog.</span>
            </div>
            </div>{{-- /filter-body --}}
        </aside>

        {{-- Items --}}
        <div>
            <div class="items-topbar">
                <form method="GET" action="{{ route($filterRoute, $slug) }}" style="display:contents;">
                    @foreach(request()->except('q') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <div class="items-search-wrap">
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="items-search" type="text" name="q" value="{{ request('q') }}" placeholder="Search items...">
                    </div>
                </form>
                {{-- Topbar form action must also use $filterRoute --}}
                <div class="items-count">
                    Showing <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong> of <strong>{{ number_format($items->total()) }}</strong> items
                </div>
            </div>

            <div class="items-grid">
                @forelse($items as $item)
                <div class="item-card">
                    <div class="item-card-head">
                        @php
                            // Arabic locale → translated family name, else the English source text.
                            $__itemKey   = 'catalog.items.' . $item->item_description;
                            $__itemTitle = (app()->getLocale() === 'ar' && Lang::has($__itemKey))
                                ? __($__itemKey)
                                : $item->item_description;
                        @endphp
                        <h3>{{ $__itemTitle }}</h3>
                        <span class="item-badge">{{ number_format($item->products) }} PRODUCTS</span>
                    </div>
                    @if($item->common_materials)
                        <div class="item-materials-label">Common Materials</div>
                        <div class="item-materials-val">{{ Str::limit($item->common_materials, 80) }}</div>
                    @endif
                    <a href="{{ route('catalog.item', [$slug, Str::slug($item->item_description)]) }}" class="item-view-btn">
                        View Specifications
                    </a>
                </div>
                @empty
                <div class="items-empty">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <p>No items match your filters. <a href="{{ route($filterRoute, $slug) }}" style="color:var(--green);">Clear filters</a></p>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($items->hasPages())
            <div class="pagination-wrap">
                @if($items->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" aria-label="Previous page">&laquo;</a>
                @endif

                @foreach($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" aria-label="Next page">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Related articles section --}}
@php
    $relatedArticles = \App\Models\Article::where('active', true)
        ->whereNotNull('slug')->where('slug', '!=', '')
        ->latest()->limit(3)->get();
    $__catIsAr = app()->getLocale() === 'ar';
@endphp
@if($relatedArticles->count())
<div class="container" style="padding-bottom:64px;">
    <div style="border-top:1px solid var(--border);padding-top:48px;">
        <h2 style="font-size:18px;font-weight:800;letter-spacing:-0.3px;margin-bottom:24px;color:var(--dark);">
            {{ $__catIsAr ? 'مقالات ذات صلة' : 'Related Articles' }}
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;">
            @foreach($relatedArticles as $ra)
            <a href="{{ route('news.show', $ra->slug) }}" style="display:block;border:1.5px solid var(--border);border-radius:12px;padding:20px;background:var(--white);text-decoration:none;color:inherit;transition:border-color .2s,box-shadow .2s;" onmouseover="this.style.borderColor='var(--green)';this.style.boxShadow='0 4px 16px rgba(0,106,59,.08)'" onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                <p style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--green);margin-bottom:8px;">{{ $ra->name_en }}</p>
                <p style="font-size:14px;font-weight:700;line-height:1.4;color:var(--dark);">{{ $__catIsAr ? $ra->title_ar : $ra->title_en }}</p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection
