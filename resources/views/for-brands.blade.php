@extends('layouts.app')

@section('title', __('for-brands.title'))

@section('description', __('for-brands.description'))

@section('og_image', 'https://www.qimta.com/images/og-for-brands.jpg')
@section('og_type', 'website')

@section('styles')
<style>
    /* -- A11Y HELPERS -- */
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    /* Latin numerals / tokens kept LTR inside RTL text */
    .en { unicode-bidi: isolate; direction: ltr; }
    /* Lists repurposed as semantic groups — strip default list chrome */
    .advantages-grid, .how-steps, .form-bullets { list-style: none; margin: 0; padding: 0; }

    /* -- HERO -- */
    .brands-hero {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: center;
        padding: 80px 0 72px;
    }
    @media (max-width: 820px) { .brands-hero { grid-template-columns: 1fr; gap: 40px; padding: 52px 0 48px; } }

    .brands-hero-eyebrow {
        font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: var(--green); margin-bottom: 18px;
        font-family: 'Cairo', sans-serif;
    }
    .brands-hero h1 {
        font-size: clamp(32px, 4.5vw, 52px); font-weight: 900; letter-spacing: -1.5px;
        line-height: 1.1; margin-bottom: 20px; color: var(--dark);
        font-family: 'Cairo', sans-serif;
    }
    /* RTL (Arabic) hero keeps the same font */
    [dir="rtl"] .brands-hero-eyebrow,
    [dir="rtl"] .brands-hero h1 {
        font-family: 'Cairo', sans-serif;
        letter-spacing: 0;
    }
    .brands-hero p {
        font-size: 15px; color: #555; line-height: 1.75; max-width: 460px; margin-bottom: 32px;
    }
    .hero-cta { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--green); color: #fff; padding: 13px 24px;
        border-radius: 10px; font-size: 14px; font-weight: 700;
        text-decoration: none; transition: background .2s, transform .15s;
        border: none; cursor: pointer;
    }
    .btn-primary:hover { background: #005a32; transform: translateY(-1px); }
    .btn-outline-dark {
        display: inline-flex; align-items: center; gap: 8px;
        border: 1.5px solid #ccc; color: #444; padding: 13px 24px;
        border-radius: 10px; font-size: 14px; font-weight: 600;
        text-decoration: none; transition: border-color .2s, color .2s;
    }
    .btn-outline-dark:hover { border-color: var(--green); color: var(--green); }

    .hero-img-wrap {
        border-radius: 20px; overflow: hidden; aspect-ratio: 4/3;
        background: linear-gradient(135deg, #1a3a2a 0%, #0d2419 100%);
        display: flex; align-items: center; justify-content: center;
        position: relative;
    }
    .hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; opacity: .85; }
    .hero-img-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(0,106,59,.4) 0%, transparent 60%);
    }

    /* -- STATS BAR -- */
    .stats-bar {
        display: grid; grid-template-columns: repeat(4, 1fr);
        border: 1.5px solid var(--border); border-radius: 16px;
        overflow: hidden; margin-bottom: 80px; background: var(--white);
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    @media (max-width: 700px) { .stats-bar { grid-template-columns: repeat(2, 1fr); } }
    .stat-cell {
        padding: 28px 24px; border-inline-end: 1.5px solid var(--border);
        text-align: center;
    }
    .stat-cell:last-child { border-inline-end: none; }
    .stat-cell .s-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 6px; }
    .stat-cell .s-val { font-size: 32px; font-weight: 900; letter-spacing: -1.5px; color: var(--green); line-height: 1; }
    .stat-cell .s-sub { font-size: 12px; color: #888; margin-top: 4px; }

    /* -- SECTION TITLE -- */
    .section-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 10px; text-align: center; }
    .section-title { font-size: clamp(24px, 3vw, 36px); font-weight: 900; letter-spacing: -0.8px; text-align: center; margin-bottom: 48px; }
    .section-title span { color: var(--green); }

    /* -- ADVANTAGES GRID -- */
    .advantages-section { margin-bottom: 88px; }
    .advantages-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    @media (max-width: 700px) { .advantages-grid { grid-template-columns: 1fr; } }
    .adv-card {
        border: 1.5px solid var(--border); border-radius: 16px; padding: 32px;
        background: var(--white); transition: border-color .2s, box-shadow .2s;
    }
    .adv-card:hover { border-color: var(--green); box-shadow: 0 4px 24px rgba(0,106,59,.08); }
    .adv-icon {
        width: 44px; height: 44px; border-radius: 12px; background: #f0fdf4;
        display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
    }
    .adv-icon svg { width: 22px; height: 22px; stroke: var(--green); fill: none; stroke-width: 1.8; }
    .adv-card h2 { font-size: 17px; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.3px; }
    .adv-card p { font-size: 13.5px; color: #666; line-height: 1.7; }

    /* -- HOW IT WORKS -- */
    .how-section {
        background: #0d1f17; border-radius: 24px; padding: 64px;
        margin-bottom: 88px; color: #fff;
    }
    @media (max-width: 700px) { .how-section { padding: 40px 28px; } }
    .how-section .section-eyebrow { color: #4ade80; }
    .how-section .section-title { color: #fff; }
    .how-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; margin-top: 48px; }
    @media (max-width: 820px) { .how-steps { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .how-steps { grid-template-columns: 1fr; } }
    .how-step { position: relative; }
    .step-num {
        width: 36px; height: 36px; border-radius: 10px; background: var(--green);
        color: #fff; font-size: 14px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    }
    .how-step h3 { font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #fff; }
    .how-step p { font-size: 13px; color: rgba(255,255,255,.6); line-height: 1.65; }

    /* -- PRICING -- */
    .pricing-section { margin-bottom: 88px; }
    .pricing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 700px) { .pricing-grid { grid-template-columns: 1fr; } }
    .pricing-card {
        border: 1.5px solid var(--border); border-radius: 20px; padding: 36px;
        background: var(--white); position: relative; transition: box-shadow .2s;
    }
    .pricing-card.featured {
        border-color: var(--green); border-width: 2px;
        box-shadow: 0 8px 40px rgba(0,106,59,.12);
    }
    .pricing-badge {
        position: absolute; top: -13px; inset-inline-end: 24px;
        background: var(--green); color: #fff;
        font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
        padding: 4px 14px; border-radius: 20px;
    }
    .pricing-tier { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 8px; }
    .pricing-card.featured .pricing-tier { color: var(--green); }
    .pricing-name { font-size: 26px; font-weight: 900; letter-spacing: -0.8px; margin-bottom: 12px; }
    .pricing-desc { font-size: 13px; color: #666; line-height: 1.65; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
    .pricing-features { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }
    .pricing-features li { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #444; }
    .pricing-features li svg { width: 16px; height: 16px; stroke: var(--green); fill: none; stroke-width: 2.5; flex-shrink: 0; }
    .pricing-btn {
        display: block; text-align: center; padding: 12px;
        border-radius: 10px; font-size: 13px; font-weight: 700;
        text-decoration: none; transition: all .2s; letter-spacing: 0.5px; text-transform: uppercase;
    }
    .pricing-btn.outline { border: 1.5px solid var(--green); color: var(--green); }
    .pricing-btn.outline:hover { background: var(--green); color: #fff; }
    .pricing-btn.solid { background: var(--green); color: #fff; }
    .pricing-btn.solid:hover { background: #005a32; }

    /* -- FORM SECTION -- */
    .form-section {
        display: grid; grid-template-columns: 1fr 1fr; gap: 64px;
        align-items: start; margin-bottom: 88px;
    }
    @media (max-width: 820px) { .form-section { grid-template-columns: 1fr; gap: 40px; } }
    .form-section-left h2 { font-size: clamp(22px, 2.8vw, 32px); font-weight: 900; letter-spacing: -0.6px; margin-bottom: 14px; }
    .form-section-left p { font-size: 14px; color: #555; line-height: 1.75; margin-bottom: 28px; }
    .form-bullets { display: flex; flex-direction: column; gap: 14px; }
    .form-bullet { display: flex; align-items: flex-start; gap: 12px; }
    .form-bullet-icon { width: 36px; height: 36px; border-radius: 10px; background: #f0fdf4; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
    .form-bullet-icon svg { width: 18px; height: 18px; stroke: var(--green); fill: none; stroke-width: 1.8; }
    .form-bullet h3 { font-size: 14px; font-weight: 700; margin-bottom: 3px; }
    .form-bullet p { font-size: 13px; color: #666; line-height: 1.55; }

    .brand-form { background: #f9fafb; border: 1.5px solid var(--border); border-radius: 20px; padding: 36px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 500px) { .form-row { grid-template-columns: 1fr; } }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 12px; font-weight: 700; color: #555; letter-spacing: 0.3px; }
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: 14px; font-family: inherit; color: var(--dark); background: #fff;
        outline: none; transition: border-color .2s;
        width: 100%;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: var(--green); }
    .form-group textarea { resize: vertical; min-height: 90px; }
    .form-submit {
        width: 100%; padding: 13px; background: var(--green); color: #fff;
        border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
        font-family: inherit; cursor: pointer; letter-spacing: 0.5px; text-transform: uppercase;
        transition: background .2s; margin-top: 4px;
    }
    .form-submit:hover { background: #005a32; }
</style>
@endsection

@push('schema')
@php
$_brandsSchema = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>'https://www.qimta.com/'],
                ['@type'=>'ListItem','position'=>2,'name'=>'For Brands','item'=>'https://www.qimta.com/for-brands'],
            ],
        ],
        [
            '@type'       => 'WebPage',
            '@id'         => 'https://www.qimta.com/for-brands#webpage',
            'url'         => 'https://www.qimta.com/for-brands',
            'name'        => __('for-brands.title'),
            'description' => __('for-brands.description'),
            'inLanguage'  => app()->getLocale(),
            'isPartOf'    => ['@id' => 'https://www.qimta.com/#website'],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_brandsSchema !!}</script>
@endpush

@section('content')

{{-- -- HERO --------------------------------------------------- --}}
{{-- H1 opens the main content; the GEO fact-block follows it (P1: h1 first, then paragraph) --}}
<div class="container">
    <div class="brands-hero">
        <div>
            <div class="brands-hero-eyebrow">{{ __('for-brands.hero.eyebrow') }}</div>
            <h1>{{ __('for-brands.hero.h1') }}</h1>

            {{-- GEO Fact Block (moved below the H1) --}}
            <p id="fact-block" style="font-size:13px;color:#777;line-height:1.75;border-inline-start:3px solid #006a3b;padding:10px 16px;background:#f9fdf9;border-radius:0 8px 8px 0;margin:0 0 24px;">
            @if(app()->getLocale() === 'ar')
                تتيح شركة كيمتا للتكنولوجيا للعلامات التجارية ومصنّعي مواد البناء إدراج بيانات منتجاتهم المعتمدة عبر {{ $catalogStats['categories'] }} فئة و{{ $catalogStats['divisions'] }} قسماً. يُفهرس كل منتج مقابل <span class="en">{{ number_format($catalogStats['products']) }}</span> رقم SKU ويُطابق مع طلبات جداول الكميات الفعلية من مقاولين وفرق مشتريات في السعودية ودول الخليج.
            @else
                Qimta Technology Company enables construction material brands and manufacturers to list verified product data across {{ $catalogStats['categories'] }} categories and {{ $catalogStats['divisions'] }} divisions. Listed products are indexed against {{ number_format($catalogStats['products']) }} SKUs and matched to live BOQ requests from contractors and procurement teams in Saudi Arabia and GCC.
            @endif
            </p>

            <p>{{ __('for-brands.hero.sub') }}</p>
            <div class="hero-cta">
                <a href="#apply" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    {{ __('for-brands.hero.cta_join') }}
                </a>
                <a href="{{ route('catalog.index') }}" class="btn-outline-dark">{{ __('for-brands.hero.cta_browse') }}</a>
            </div>
        </div>
        <div class="hero-img-wrap">
            <img src="https://images.unsplash.com/photo-1565043589221-1a6fd9ae45c7?w=800&h=600&q=80&auto=format&fit=crop" alt="{{ __('for-brands.hero.img_alt') }}" width="800" height="600" loading="eager" decoding="async" referrerpolicy="no-referrer" onerror="this.style.display='none'">
            <div class="hero-img-overlay"></div>
        </div>
    </div>
</div>

{{-- -- STATS BAR --------------------------------------------- --}}
<section class="container" aria-labelledby="fb-stats">
    <h2 id="fb-stats" class="sr-only">{{ __('for-brands.stats.heading') }}</h2>
    <div class="stats-bar">
        @php
                $divisions  = $catalogStats['divisions'];
                $categories = $catalogStats['categories'];
                $products   = $catalogStats['products'];
        @endphp
        <div class="stat-cell">
            <h3 class="s-label">{{ __('for-brands.stats.divisions_label') }}</h3>
            <div class="s-val"><span class="en">{{ $divisions }}</span></div>
            <div class="s-sub">{{ __('for-brands.stats.divisions_sub') }}</div>
        </div>
        <div class="stat-cell">
            <h3 class="s-label">{{ __('for-brands.stats.categories_label') }}</h3>
            <div class="s-val"><span class="en">{{ number_format($categories) }}</span></div>
            <div class="s-sub">{{ __('for-brands.stats.categories_sub') }}</div>
        </div>
        <div class="stat-cell">
            <h3 class="s-label">{{ __('for-brands.stats.products_label') }}</h3>
            <div class="s-val"><span class="en">{{ number_format($products) }}</span></div>
            <div class="s-sub">{{ __('for-brands.stats.products_sub') }}</div>
        </div>
        <div class="stat-cell">
            <h3 class="s-label">{{ __('for-brands.stats.specs_label') }}</h3>
            <div class="s-val"><span class="en">{{ __('for-brands.stats.specs_val') }}</span></div>
            <div class="s-sub">{{ __('for-brands.stats.specs_sub') }}</div>
        </div>
    </div>
</section>

{{-- -- THE QIMTA ADVANTAGE ------------------------------------ --}}
<section class="container" aria-labelledby="fb-why">
    <div class="advantages-section">
        <p class="section-eyebrow">{{ __('for-brands.adv.eyebrow') }}</p>
        <h2 id="fb-why" class="section-title">{{ __('for-brands.adv.title') }}<span>{{ __('for-brands.adv.highlight') }}</span></h2>
        <ul class="advantages-grid">
            <li class="adv-card">
                <div class="adv-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>{{ __('for-brands.adv.demand_title') }}</h3>
                <p>{{ __('for-brands.adv.demand_desc') }}</p>
            </li>
            <li class="adv-card">
                <div class="adv-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3>{{ __('for-brands.adv.identity_title') }}</h3>
                <p>{{ __('for-brands.adv.identity_desc') }}</p>
            </li>
            <li class="adv-card">
                <div class="adv-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                </div>
                <h3>{{ __('for-brands.adv.visibility_title') }}</h3>
                <p>{{ __('for-brands.adv.visibility_desc') }}</p>
            </li>
            <li class="adv-card">
                <div class="adv-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <h3>{{ __('for-brands.adv.path_title') }}</h3>
                <p>{{ __('for-brands.adv.path_desc') }}</p>
            </li>
        </ul>
    </div>
</section>

{{-- -- HOW IT WORKS ------------------------------------------- --}}
<section class="container" aria-labelledby="fb-how">
    <div class="how-section">
        <p class="section-eyebrow">{{ __('for-brands.how.eyebrow') }}</p>
        <h2 id="fb-how" class="section-title">{{ __('for-brands.how.title') }}</h2>
        <ol class="how-steps">
            <li class="how-step">
                <div class="step-num" aria-hidden="true">1</div>
                <h3>{{ __('for-brands.how.step1_title') }}</h3>
                <p>{{ __('for-brands.how.step1_desc') }}</p>
            </li>
            <li class="how-step">
                <div class="step-num" aria-hidden="true">2</div>
                <h3>{{ __('for-brands.how.step2_title') }}</h3>
                <p>{{ __('for-brands.how.step2_desc') }}</p>
            </li>
            <li class="how-step">
                <div class="step-num" aria-hidden="true">3</div>
                <h3>{{ __('for-brands.how.step3_title') }}</h3>
                <p>{{ __('for-brands.how.step3_desc') }}</p>
            </li>
            <li class="how-step">
                <div class="step-num" aria-hidden="true">4</div>
                <h3>{{ __('for-brands.how.step4_title') }}</h3>
                <p>{{ __('for-brands.how.step4_desc') }}</p>
            </li>
        </ol>
    </div>
</section>

{{-- -- MARKET POSITIONING ------------------------------------- --}}
<section class="container" aria-labelledby="fb-plans">
    <div class="pricing-section">
        <p class="section-eyebrow">{{ __('for-brands.pricing.eyebrow') }}</p>
        <h2 id="fb-plans" class="section-title">{{ __('for-brands.pricing.title') }}<span>{{ __('for-brands.pricing.highlight') }}</span></h2>
        <div class="pricing-grid">
            <article class="pricing-card" aria-label="{{ __('for-brands.pricing.standard_name') }}">
                <p class="pricing-tier">{{ __('for-brands.pricing.standard_tier') }}</p>
                <h3 class="pricing-name">{{ __('for-brands.pricing.standard_name') }}</h3>
                <p class="pricing-desc">{{ __('for-brands.pricing.standard_desc') }}</p>
                <ul class="pricing-features">
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.standard_f1') }}</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.standard_f2') }}</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.standard_f3') }}</li>
                </ul>
                <a href="#apply" class="pricing-btn outline">{{ __('for-brands.pricing.standard_cta') }}</a>
            </article>
            <article class="pricing-card featured" aria-label="{{ __('for-brands.pricing.premium_name') }} — {{ __('for-brands.pricing.recommended') }}">
                <p class="pricing-badge">{{ __('for-brands.pricing.recommended') }}</p>
                <p class="pricing-tier">{{ __('for-brands.pricing.premium_tier') }}</p>
                <h3 class="pricing-name">{{ __('for-brands.pricing.premium_name') }}</h3>
                <p class="pricing-desc">{{ __('for-brands.pricing.premium_desc') }}</p>
                <ul class="pricing-features">
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.premium_f1') }}</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.premium_f2') }}</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.premium_f3') }}</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {{ __('for-brands.pricing.premium_f4') }}</li>
                </ul>
                <a href="#apply" class="pricing-btn solid">{{ __('for-brands.pricing.premium_cta') }}</a>
            </article>
        </div>
    </div>
</section>

{{-- -- FAQ ---------------------------------------------------- --}}
<x-faq id="brands-faq" :items="[
    ['q' => __('for-brands.faq.q1'), 'a' => __('for-brands.faq.a1')],
    ['q' => __('for-brands.faq.q2'), 'a' => __('for-brands.faq.a2')],
    ['q' => __('for-brands.faq.q3'), 'a' => __('for-brands.faq.a3')],
    ['q' => __('for-brands.faq.q4'), 'a' => __('for-brands.faq.a4')],
    ['q' => __('for-brands.faq.q5'), 'a' => __('for-brands.faq.a5')],
]" />

{{-- -- APPLICATION FORM --------------------------------------- --}}
<section class="container" id="apply" aria-labelledby="fb-apply">
    <div class="form-section">
        <div class="form-section-left">
            <h2 id="fb-apply">{{ __('for-brands.form.title') }}</h2>
            <p>{{ __('for-brands.form.sub') }}</p>
            <ul class="form-bullets">
                <li class="form-bullet">
                    <div class="form-bullet-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div>
                        <h3>{{ __('for-brands.form.bullet1_title') }}</h3>
                        <p>{{ __('for-brands.form.bullet1_desc') }}</p>
                    </div>
                </li>
                <li class="form-bullet">
                    <div class="form-bullet-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>{{ __('for-brands.form.bullet2_title') }}</h3>
                        <p>{{ __('for-brands.form.bullet2_desc') }}</p>
                    </div>
                </li>
            </ul>
        </div>

        <div class="brand-form">
            {{-- TODO: wire a real POST route (e.g. /{locale}/for-brands/apply) before launch --}}
            <form action="#" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="fb-company">{{ __('for-brands.form.company') }}</label>
                        <input type="text" id="fb-company" name="company" autocomplete="organization" placeholder="{{ __('for-brands.form.company_ph') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="fb-contact">{{ __('for-brands.form.contact') }}</label>
                        <input type="text" id="fb-contact" name="contact" autocomplete="name" placeholder="{{ __('for-brands.form.contact_ph') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="fb-email">{{ __('for-brands.form.email') }}</label>
                    <input type="email" id="fb-email" name="email" autocomplete="email" placeholder="{{ __('for-brands.form.email_ph') }}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="fb-category">{{ __('for-brands.form.category') }}</label>
                        <select id="fb-category" name="category">
                            <option>{{ __('for-brands.form.cat_steel') }}</option>
                            <option>{{ __('for-brands.form.cat_fire') }}</option>
                            <option>{{ __('for-brands.form.cat_elec') }}</option>
                            <option>{{ __('for-brands.form.cat_hvac') }}</option>
                            <option>{{ __('for-brands.form.cat_plumb') }}</option>
                            <option>{{ __('for-brands.form.cat_civil') }}</option>
                            <option>{{ __('for-brands.form.cat_other') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fb-region">{{ __('for-brands.form.region') }}</label>
                        {{-- Region scoped to Saudi Arabia & GCC to match the page's target market --}}
                        <select id="fb-region" name="region">
                            <option>{{ __('for-brands.form.reg_ksa') }}</option>
                            <option>{{ __('for-brands.form.reg_gcc') }}</option>
                            <option>{{ __('for-brands.form.reg_other') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="fb-message">{{ __('for-brands.form.message') }}</label>
                    <textarea id="fb-message" name="message" placeholder="{{ __('for-brands.form.message_ph') }}"></textarea>
                </div>
                <button type="submit" class="form-submit">{{ __('for-brands.form.submit') }}</button>
            </form>
        </div>
    </div>
</section>

@endsection
