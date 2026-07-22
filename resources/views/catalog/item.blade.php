@extends('layouts.app')

@section('title', $itemDescription . ' — BOQ Price & Specs | ' . $division . ' | Qimta')

@section('description', $itemDescription . ' — BOQ pricing, technical specifications, material types, and lead times. Part of Qimta’s verified ' . $division . ' catalog for Saudi Arabia and GCC construction projects.')

@section('styles')
<style>
    /* ── BREADCRUMB ── */
    .breadcrumb { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #999; margin-bottom: 32px; flex-wrap: wrap; }
    .breadcrumb a { color: #999; transition: color .2s; }
    .breadcrumb a:hover { color: var(--green); }
    .breadcrumb svg { width: 12px; height: 12px; stroke: #bbb; fill: none; stroke-width: 2; flex-shrink: 0; }
    .breadcrumb span { color: var(--green); }

    /* ── HERO ── */
    .item-hero { padding: 40px 0 48px; text-align: center; }
    .item-hero-text h1 { font-size: clamp(22px, 3vw, 34px); font-weight: 800; letter-spacing: -0.8px; line-height: 1.2; margin-bottom: 16px; }
    .item-hero-text p { font-size: 14px; color: #555; line-height: 1.7; max-width: 640px; margin-left: auto; margin-right: auto; }

    /* ── OVERVIEW + MATRIX ── */
    .item-body { display: grid; grid-template-columns: 200px minmax(0, 760px); gap: 24px; margin-bottom: 40px; justify-content: center; align-items: start; }
    @media (max-width: 820px) { .item-body { grid-template-columns: 1fr; } }

    .overview-card { border: 1.5px solid var(--border); border-radius: 14px; padding: 24px; background: var(--white); text-align: center; }
    .overview-card .ov-label { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 3px; margin-top: 16px; }
    .overview-card .ov-label:first-child { margin-top: 0; }
    .overview-card .ov-title { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 12px; }
    .overview-card .ov-val { font-size: 13px; font-weight: 700; color: var(--dark); line-height: 1.4; }
    .overview-card .ov-lead { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 12px; color: #666; }
    .overview-card .ov-lead svg { width: 14px; height: 14px; stroke: #aaa; fill: none; stroke-width: 2; flex-shrink: 0; }

    .matrix-card { border: 1.5px solid var(--border); border-radius: 14px; padding: 28px; background: var(--white); text-align: center; }
    .matrix-card .mat-title { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 20px; }
    .matrix-section { margin-bottom: 20px; }
    .matrix-section:last-child { margin-bottom: 0; }
    .matrix-section-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #aaa; margin-bottom: 8px; }
    .chip-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
    .chip { display: inline-block; padding: 5px 12px; border: 1.5px solid var(--border); border-radius: 20px; font-size: 12px; font-weight: 600; color: #444; background: #fafafa; }
    .chip.active { border-color: var(--green); color: var(--green); background: #f0fdf4; }
    .matrix-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 16px; }
    @media (max-width: 600px) { .matrix-grid { grid-template-columns: 1fr; } }
    .matrix-cell-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #aaa; margin-bottom: 5px; }
    .matrix-cell-val { font-size: 13px; color: #444; }

    /* ── USE CASES ── */
    .use-cases { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 48px; }
    @media (max-width: 700px) { .use-cases { grid-template-columns: 1fr; } }
    .use-case-card { border: 1.5px solid var(--border); border-radius: 14px; padding: 24px; background: var(--white); text-align: center; }
    .use-case-card .uc-icon { width: 32px; height: 32px; margin-bottom: 12px; margin-left: auto; margin-right: auto; }
    .use-case-card .uc-icon svg { width: 32px; height: 32px; stroke: var(--green); fill: none; stroke-width: 1.5; }
    .use-case-card h2 { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
    .use-case-card p { font-size: 13px; color: #666; line-height: 1.6; }

    /* ── STANDARDS ── */
    .standards-section { background: #f9fafb; border-radius: 16px; padding: 40px; text-align: center; margin-bottom: 48px; }
    .standards-section .std-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 10px; }
    .standards-section h2 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 28px; }
    .std-badges { display: flex; justify-content: center; flex-wrap: wrap; gap: 20px; }
    .std-badge { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .std-badge .std-icon { width: 44px; height: 44px; background: var(--white); border: 1.5px solid var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .std-badge .std-icon svg { width: 22px; height: 22px; stroke: var(--green); fill: none; stroke-width: 1.5; }
    .std-badge span { font-size: 11px; font-weight: 700; color: #555; }

    /* ── CTA ── */
    .cta-block { background: var(--green); border-radius: 16px; padding: 48px 40px; text-align: center; margin-bottom: 48px; }
    .cta-block h2 { font-size: clamp(18px, 2.5vw, 26px); font-weight: 800; color: var(--white); margin-bottom: 12px; letter-spacing: -0.3px; }
    .cta-block p { font-size: 13px; color: rgba(255,255,255,.8); margin-bottom: 24px; max-width: 480px; margin-left: auto; margin-right: auto; }
    .cta-block a { display: inline-block; background: var(--white); color: var(--green); font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 8px; text-decoration: none; transition: opacity .2s; }
    .cta-block a:hover { opacity: .9; }
    .cta-block .cta-meta { font-size: 11px; color: rgba(255,255,255,.55); margin-top: 14px; letter-spacing: 0.5px; text-transform: uppercase; }

    /* ── FAQ ── */
    .faq-section { display: grid; grid-template-columns: 240px minmax(0, 640px); gap: 40px; margin-bottom: 56px; justify-content: center; align-items: start; }
    @media (max-width: 820px) { .faq-section { grid-template-columns: 1fr; text-align: center; } }
    .faq-section-left h3 { font-size: 20px; font-weight: 800; letter-spacing: -0.3px; margin-bottom: 8px; }
    .faq-section-left p { font-size: 13px; color: #666; line-height: 1.65; }
    .faq-list { display: flex; flex-direction: column; }
    .faq-item { border-bottom: 1px solid var(--border); }
    .faq-item summary { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; font-size: 14px; font-weight: 600; cursor: pointer; list-style: none; color: var(--dark); }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-item summary svg { width: 16px; height: 16px; stroke: #aaa; fill: none; stroke-width: 2; flex-shrink: 0; transition: transform .2s; }
    .faq-item[open] summary svg { transform: rotate(180deg); }
    .faq-item .faq-answer { font-size: 13px; color: #555; line-height: 1.7; padding-bottom: 16px; }

    /* ── RELATED ── */
    .related-section { margin-bottom: 56px; text-align: center; }
    .related-section h3 { font-size: 18px; font-weight: 800; letter-spacing: -0.3px; margin-bottom: 20px; }
    .related-tags { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
    .related-tag { display: inline-block; padding: 8px 18px; border: 1.5px solid var(--border); border-radius: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: #444; text-decoration: none; transition: border-color .2s, color .2s, background .2s; }
    .related-tag:hover { border-color: var(--green); color: var(--green); background: #f0fdf4; }

    /* ── BOTTOM CTA ── */
    .bottom-cta { text-align: center; padding: 32px 0 64px; }
    .bottom-cta a { display: inline-block; border: 1.5px solid var(--green); color: var(--green); font-weight: 700; font-size: 13px; padding: 11px 28px; border-radius: 8px; text-decoration: none; transition: background .2s, color .2s; }
    .bottom-cta a:hover { background: var(--green); color: var(--white); }
</style>
@endsection

@section('content')
@php
$__divSlugForSchema = \Illuminate\Support\Str::slug($division);
$_breadcrumb = json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>'https://www.qimta.com/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Construction Catalog','item'=>'https://www.qimta.com/catalog'],
        ['@type'=>'ListItem','position'=>3,'name'=>$division,'item'=>'https://www.qimta.com/catalog/'.$__divSlugForSchema],
        ['@type'=>'ListItem','position'=>4,'name'=>$itemDescription,'item'=>'https://www.qimta.com'.request()->getPathInfo()],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_breadcrumb !!}</script>
@php
$_productSchema = json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $itemDescription,
    'description' => $itemDescription.' — BOQ pricing, technical specifications, material types, and lead times. Part of Qimta\'s verified '.$division.' catalog for Saudi Arabia and GCC construction projects.',
    'category'    => $division,
    'brand'       => ['@type'=>'Brand','name'=>$division],
    'url'         => 'https://www.qimta.com'.request()->getPathInfo(),
    'offers'      => [
        '@type'        => 'Offer',
        'priceCurrency'=> 'SAR',
        'availability' => 'https://schema.org/InStock',
        'seller'       => ['@type'=>'Organization','name'=>'Qimta Technology Company','url'=>'https://www.qimta.com'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_productSchema !!}</script>
    <div style="padding-top:32px;">
        @php $__divSlug = \Illuminate\Support\Str::slug($division); @endphp
        <nav aria-label="{{ __('catalog.item.breadcrumb_aria') }}" class="breadcrumb">
            <a href="/">{{ __('catalog.item.home') }}</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="{{ route('catalog.index') }}">{{ __('catalog.item.catalog') }}</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="{{ route('catalog.division', $__divSlug) }}">{{ $divisionLabel ?? $division }}</a>
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span aria-current="page">{{ $itemLabel ?? $itemDescription }}</span>
        </nav>
    </div>

    {{-- Hero --}}
    <div class="item-hero">
        <div class="item-hero-text">
            <h1>{{ $itemLabel ?? $itemDescription }} —<br>{{ __('catalog.item.hero_suffix') }}</h1>
            <p>
                {{ __('catalog.item.hero_intro', ['item' => ($itemLabel ?? $itemDescription), 'division' => ($divisionLabel ?? $division)]) }}
            </p>
        </div>
    </div>

    {{-- Overview + Matrix --}}
    <div class="item-body">

        {{-- Item Overview --}}
        <div class="overview-card">
            <div class="ov-title">{{ __('catalog.item.overview_title') }}</div>
            <div class="ov-label">{{ __('catalog.item.division') }}</div>
            <div class="ov-val">{{ $divisionLabel ?? catalog_value_t('divisions', $division) }}</div>
            @if($product->category)
            <div class="ov-label">{{ __('catalog.item.category') }}</div>
            <div class="ov-val">{{ $product->category }}</div>
            @endif
            @if($product->lead_time)
            <div class="ov-label">{{ __('catalog.item.pricing_layer') }}</div>
            <div class="ov-val"><span class="en">{{ $product->lead_time }}</span></div>
            @endif
            <div class="ov-lead">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                {{ __('catalog.item.lead_time') }}
            </div>
        </div>

        {{-- Technical Configuration Matrix --}}
        <div class="matrix-card">
            <div class="mat-title">{{ __('catalog.item.matrix_title') }}</div>

            @if($materials->isNotEmpty())
            <div class="matrix-section">
                <div class="matrix-section-label">{{ __('catalog.item.available_materials') }}</div>
                <div class="chip-row">
                    @foreach($materials as $mat)
                        <span class="chip">{{ $mat }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($sizes->isNotEmpty())
            <div class="matrix-section">
                <div class="matrix-section-label">{{ __('catalog.item.size_range') }}</div>
                <div class="chip-row">
                    @foreach($sizes as $sz)
                        <span class="chip">{{ $sz }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="matrix-grid">
                @if($product->category)
                <div>
                    <div class="matrix-cell-label">{{ __('catalog.item.category') }}</div>
                    <div class="matrix-cell-val">{{ $product->category }}</div>
                </div>
                @endif
                @if($product->sub_type)
                <div>
                    <div class="matrix-cell-label">{{ __('catalog.item.type') }}</div>
                    <div class="matrix-cell-val">{{ $product->sub_type }}</div>
                </div>
                @endif
                <div>
                    <div class="matrix-cell-label">{{ __('catalog.item.products_indexed') }}</div>
                    <div class="matrix-cell-val"><span class="en">{{ number_format($product->product_count) }}</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Use Cases --}}
    <div class="use-cases">
        <div class="use-case-card">
            <div class="uc-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h2>{{ __('catalog.item.uc_performance_title') }}</h2>
            <p>{{ __('catalog.item.uc_performance_body', ['division' => ($divisionLabel ?? $division), 'item' => ($itemLabel ?? $itemDescription)]) }}</p>
        </div>
        <div class="use-case-card">
            <div class="uc-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <h2>{{ __('catalog.item.uc_pressure_title') }}</h2>
            <p>{{ __('catalog.item.uc_pressure_body') }}</p>
        </div>
        <div class="use-case-card">
            <div class="uc-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <h2>{{ __('catalog.item.uc_boq_title') }}</h2>
            <p>{{ __('catalog.item.uc_boq_body') }}</p>
        </div>
    </div>

    {{-- Standards --}}
    <div class="standards-section">
        <div class="std-eyebrow">{{ __('catalog.item.std_eyebrow') }}</div>
        <h2>{{ __('catalog.item.std_title') }}</h2>
        <div class="std-badges">
            @foreach([[__('catalog.item.std_ul'),'M'], [__('catalog.item.std_fm'),'person'], [__('catalog.item.std_en'),'euro'], [__('catalog.item.std_iso'),'globe'], [__('catalog.item.std_spec'),'file']] as [$label, $icon])
            <div class="std-badge">
                <div class="std-icon">
                    @if($icon === 'M')
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @elseif($icon === 'person')
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @elseif($icon === 'euro')
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    @elseif($icon === 'globe')
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    @else
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    @endif
                </div>
                <span>{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div class="cta-block">
        <h2>{{ __('catalog.item.cta_title') }}</h2>
        <p>{{ __('catalog.item.cta_body', ['item' => ($itemLabel ?? $itemDescription)]) }}</p>
        <a href="{{ route('enduser.register') }}">{{ __('catalog.item.cta_button') }}</a>
        <div class="cta-meta">{{ __('catalog.item.cta_meta') }}</div>
    </div>

    {{-- FAQ --}}
    <div class="faq-section">
        <div class="faq-section-left">
            <h3>{{ __('catalog.item.faq_title') }}</h3>
            <p>{{ __('catalog.item.faq_intro', ['item' => ($itemLabel ?? $itemDescription)]) }}</p>
        </div>
        <div class="faq-list">
            @php
                $__item = $itemLabel ?? $itemDescription;
                $__div  = $divisionLabel ?? $division;
                $faqs = [
                    [__('catalog.item.faq_q1', ['item' => $__item]),
                     __('catalog.item.faq_a1', ['item' => $__item, 'division' => $__div])],
                    [__('catalog.item.faq_q2'),
                     $materials->isNotEmpty()
                        ? __('catalog.item.faq_a2_with', ['materials' => $materials->join(($isAr ?? false) ? '، ' : ', ')])
                        : __('catalog.item.faq_a2_without')],
                    [__('catalog.item.faq_q3'), __('catalog.item.faq_a3')],
                    [__('catalog.item.faq_q4'), __('catalog.item.faq_a4')],
                    [__('catalog.item.faq_q5'), __('catalog.item.faq_a5', ['item' => $__item])],
                    [__('catalog.item.faq_q6'), __('catalog.item.faq_a6', ['item' => $__item])],
                ];
            @endphp
            @foreach($faqs as [$q, $a])
            <details class="faq-item">
                <summary>
                    {{ $q }}
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-answer">{{ $a }}</div>
            </details>
            @endforeach
        </div>
    </div>

    {{-- Related Items (from database) --}}
    @if($related->isNotEmpty())
    <div class="related-section">
        <h3>{{ __('catalog.item.related_title', ['division' => ($divisionLabel ?? $division)]) }}</h3>
        <div class="related-tags">
            @foreach($related as $rel)
                @continue(!$rel->slug)
                <a href="{{ route('catalog.item', [$divisionSlug, $rel->slug]) }}" class="related-tag">
                    {{ ($isAr ?? false) ? $rel->name : strtoupper($rel->name) }}
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Bottom CTA --}}
    <div class="bottom-cta">
        <a href="{{ route('enduser.register') }}">{{ __('catalog.item.bottom_cta') }}</a>
    </div>

</div>
@endsection
