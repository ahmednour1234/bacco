@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', $isAr
    ? 'كيمتا — منصة تسعير جداول الكميات | السعودية والخليج'
    : 'Qimta — Construction BOQ Pricing Platform | Saudi Arabia & GCC')

@section('description', $isAr
    ? 'كيمتا: سعر كل بند من جدول الكميات في دقائق. ' . number_format($catalogStats['products']) . ' منتجاً موثّقاً ومليارات المواصفات التقنية. منصة تسعير مشاريع البناء للسعودية والخليج.'
    : 'Qimta prices every BOQ line across every brand in seconds. Access ' . number_format($catalogStats['products']) . ' verified products and 1B technical specs. The construction pricing platform for Saudi Arabia & GCC.')

@section('nav-cta')
    <a href="{{ route('enduser.login') }}" class="btn-nav-cta">
        {{ __('welcome.nav.price_boq') }}
        <span class="cta-badge">{{ $isAr ? 'مجاني' : 'FREE' }}</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
@endsection

@section('mobile-cta')
    <a href="{{ route('enduser.login') }}" class="btn btn-primary">{{ __('welcome.nav.price_boq') }}</a>
@endsection

@section('styles')
<style>
    /* -- HERO -- */
    .hero {
        padding: 105px 0 90px;
        background: var(--white);
        overflow: hidden;
    }

    .hero-inner {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 70px;
        align-items: center;
    }

    .hero-content {
        max-width: 650px;
    }

    .hero-tag {
        font-family: 'Cairo', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: 3px;
        color: var(--green);
        text-transform: uppercase;
        margin-bottom: 22px;
        line-height: 1.4;
    }

    [dir="rtl"] .hero-tag {
        letter-spacing: 0;
    }

    .hero-tag::before {
        content: "";
        width: 34px;
        height: 3px;
        border-radius: 50px;
        background: var(--green);
        flex-shrink: 0;
    }

    .hero h1 {
        font-family: 'Cairo', sans-serif;
        font-size: clamp(44px, 5.4vw, 78px);
        font-weight: 950;
        line-height: .98;
        letter-spacing: -3px;
        margin-bottom: 18px;
        color: var(--dark);
    }

    [dir="rtl"] .hero h1 {
        letter-spacing: 0;
        line-height: 1.12;
    }

    .hero-sub {
        font-size: 15px;
        color: var(--gray-text);
        max-width: 500px;
        margin-bottom: 30px;
        line-height: 1.45;
    }

    .hero-btns {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
    }

    .hero-btns .btn-lg {
        min-height: 58px;
        padding: 0 30px;
        font-size: 16px;
        font-weight: 900;
        border-radius: 12px;
    }

    .hero-btns .btn-dark {
        box-shadow: 0 16px 34px rgba(0, 0, 0, .18);
        transform: translateY(0);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .hero-btns .btn-dark:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 42px rgba(0, 0, 0, .22);
    }

    .hero-mockup {
        position: relative;
        background: linear-gradient(145deg, #eef7f2 0%, #cfe5da 100%);
        border-radius: 24px;
        padding: 30px;
        min-height: 390px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .13);
        border: 1px solid rgba(0, 106, 59, .12);
    }

    .hero-mockup::before {
        content: "";
        position: absolute;
        inset: -18px;
        border-radius: 32px;
        background: rgba(0, 134, 76, .07);
        z-index: -1;
    }

    .mock-header {
        background: var(--white);
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 14px;
        font-weight: 800;
        color: var(--dark);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .04);
    }

    .mock-badge {
        background: var(--green-btn);
        color: var(--white);
        font-size: 10px;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 800;
    }

    .mock-row {
        background: rgba(255, 255, 255, .92);
        border-radius: 10px;
        padding: 13px 16px;
        display: grid;
        grid-template-columns: 1.5fr .8fr .8fr;
        gap: 10px;
        align-items: center;
        font-size: 13px;
        color: #555;
        border: 1px solid rgba(0, 0, 0, .035);
    }

    .mock-row span:first-child {
        font-weight: 700;
        color: var(--dark);
    }

    .mock-row span:last-child {
        font-weight: 900;
        color: var(--green);
        text-align: end;
    }

    .mock-actions {
        display: flex;
        gap: 10px;
        margin: 3px 0;
    }

    .mock-btn,
    .mock-btn-alt {
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 900;
    }

    .mock-btn {
        background: #f0a800;
        color: var(--white);
    }

    .mock-btn-alt {
        background: var(--white);
        color: #555;
        border: 1px solid rgba(0, 0, 0, .06);
    }

    /* -- STATS -- */
    .stats { background: rgba(0, 134, 76, 0.30); padding: 64px 0; }
    .stats-label { font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 800; letter-spacing: 4px; color: var(--green); text-transform: uppercase; margin-bottom: 6px; line-height: 24px; }
    [dir="rtl"] .stats-label { letter-spacing: 0; }
    .stats-sub { font-size: 13px; color: #444; margin-bottom: 0; }
    .stats-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
    .stats-link { font-family: 'Cairo', sans-serif; font-size: 13px; font-weight: 700; color: var(--green); display: flex; align-items: center; gap: 6px; margin-top: 4px; white-space: nowrap; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card { background: var(--white); border-radius: 12px; padding: 28px 24px; }
    .stat-icon { font-size: 18px; margin-bottom: 10px; color: #444; }
    .stat-label { font-family: 'Cairo', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #555; margin-bottom: 8px; }
    [dir="rtl"] .stat-label { letter-spacing: 0; }
    .stat-value { font-family: 'Cairo', sans-serif; font-size: 54px; font-weight: 900; color: var(--dark); letter-spacing: -2px; line-height: 1; }
    [dir="rtl"] .stat-value { letter-spacing: 0; }
    .stat-line { width: 36px; height: 3px; background: var(--green); margin-top: 14px; border-radius: 2px; }

    /* -- PROBLEM -- */
    .problem { padding: 80px 0; background: var(--white); }
    .section-intro { font-size: 14px; color: var(--gray-text); max-width: 520px; margin-bottom: 48px; line-height: 1.65; }
    .section-intro strong { display: block; font-size: 20px; font-weight: 700; margin-bottom: 6px; color: var(--dark); }
    .problem-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .problem-card { border: 1px solid var(--border); border-radius: 16px; padding: 36px 28px; }
    .problem-icon { width: 52px; height: 52px; background: rgba(0,106,59,.08); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 22px; color: var(--green); }
    .problem-icon svg { width: 24px; height: 24px; stroke: var(--green); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .problem-title { font-size: 17px; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .problem-desc { font-size: 13px; color: var(--gray-text); line-height: 1.65; }

    /* -- HOW IT WORKS -- */
    .how { padding: 80px 0; background: var(--white); }
    .section-title { font-family: 'Cairo', sans-serif; font-size: 25px; font-weight: 800; color: var(--dark); text-align: left; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
    [dir="rtl"] .section-title { text-align: right; }
    .section-title::before { content: ''; display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--green); flex-shrink: 0; }
    .how-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; align-items: stretch; }
    .how-card { background: var(--white); border-radius: 16px; padding: 32px 24px; border: 1px solid var(--border); display: flex; flex-direction: column; position: relative; overflow: hidden; min-height: 260px; }
    .how-card.active { background: var(--green); color: var(--white); border-color: transparent; }
    .how-card.active::after { content: '\26A1'; position: absolute; bottom: -10px; right: -4px; font-size: 120px; opacity: 0.12; line-height: 1; color: #f0a800; pointer-events: none; }
    [dir="rtl"] .how-card.active::after { right: auto; left: -4px; }
    .how-num { font-family: 'Cairo', sans-serif; font-size: 18px; font-weight: 800; letter-spacing: 1px; color: #bbb; margin-bottom: 16px; }
    .how-card.active .how-num { color: rgba(255,255,255,.75); }
    .how-title { font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 800; margin-bottom: 10px; color: var(--dark); }
    .how-card.active .how-title { color: var(--white); }
    .how-desc { font-size: 13px; color: var(--gray-text); line-height: 1.65; }
    .how-card.active .how-desc { color: rgba(255,255,255,.95); }
    .how-icon { width: 52px; height: 52px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; }
    .how-icon svg { width: 32px; height: 32px; stroke: var(--dark); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
    .how-card.active .how-icon svg { stroke: #f0a800; }

    /* -- PILLARS -- */
    .pillars { background: var(--green); padding: 40px 0; }
    .pillars-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: rgba(255,255,255,.08); }
    .pillar { padding: 48px 40px; background: var(--green); }
    .pillar-icon { width: 44px; height: 44px; margin-bottom: 16px; display: flex; align-items: center; justify-content: center; }
    .pillar-icon svg { width: 28px; height: 28px; stroke: rgba(255,255,255,.8); fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
    .pillar-num { font-family: 'Cairo', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: rgba(255,255,255,.5); text-transform: uppercase; margin-bottom: 10px; }
    [dir="rtl"] .pillar-num { letter-spacing: 0; }
    .pillar-title { font-family: 'Cairo', sans-serif; font-size: 22px; font-weight: 900; color: var(--white); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
    [dir="rtl"] .pillar-title { letter-spacing: 0; }
    .pillar-desc { font-size: 12px; color: rgba(255,255,255,.7); line-height: 1.6; }

    /* -- ECOSYSTEM -- */
    .eco { padding: 80px 0; background: var(--white); }
    .eco-header { margin-bottom: 48px; }
    .eco-label { font-size: 28px; font-weight: 900; color: var(--dark); margin-bottom: 12px; }
    .eco-sub { font-size: 15px; color: var(--gray-text); line-height: 1.65; }
    .eco-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .eco-card { border: 1px solid var(--border); border-radius: 16px; padding: 36px 32px; display: flex; flex-direction: column; }
    .eco-icon { width: 64px; height: 64px; border-radius: 50%; background: rgba(0,106,59,.09); display: flex; align-items: center; justify-content: center; margin-bottom: 24px; flex-shrink: 0; }
    .eco-icon svg { width: 28px; height: 28px; stroke: var(--green); fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
    .eco-title { font-size: 18px; font-weight: 800; color: var(--dark); margin-bottom: 12px; }
    .eco-desc { font-size: 14px; color: var(--gray-text); margin-bottom: 24px; line-height: 1.7; flex: 1; }
    .eco-link { font-size: 14px; font-weight: 700; color: var(--green); display: inline-flex; align-items: center; gap: 6px; margin-top: auto; }
    .eco-link:hover { text-decoration: underline; }
    .eco-card-toggle { display: none; }
    @media (max-width: 768px) {
        .eco-grid { grid-template-columns: 1fr; }
        .eco-card-toggle { display: flex; align-items: center; justify-content: space-between; }
        .eco-card-toggle svg { width: 18px; height: 18px; stroke: var(--green); fill: none; stroke-width: 2; transition: transform .25s; flex-shrink: 0; }
        .eco-card-toggle svg.open { transform: rotate(180deg); }
    }

    /* -- COMPARISON TABLE -- */
    .compare { padding: 80px 0; background: var(--cream); }
    .compare-wrap { border-radius: 20px; padding: 8px; background: var(--white); overflow-x: auto; box-shadow: 0 4px 24px rgba(0,0,0,.07); }
    .compare table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 14px; overflow: hidden; min-width: 540px; font-family: 'Cairo', sans-serif; }
    .compare th, .compare td { padding: 22px 32px; text-align: left; font-size: 14px; border-bottom: 1px solid var(--border); vertical-align: middle; height: 64px; }
    [dir="rtl"] .compare th, [dir="rtl"] .compare td { text-align: right; }
    .compare th { font-family: 'Cairo', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #bbb; background: var(--white); }
    [dir="rtl"] .compare th { letter-spacing: 0; }
    .compare th.col-qimta { background: var(--green); color: var(--white); text-align: center; font-family: 'Cairo', sans-serif; font-weight: 900; font-size: 16px; letter-spacing: 0; text-transform: none; }
    .compare td { font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: 800; color: var(--dark); }
    .compare td.col-qimta { background: var(--green); text-align: center; font-family: 'Cairo', sans-serif; font-weight: 900; font-size: 15px; color: var(--white); border-bottom: 1px solid rgba(255,255,255,.12); }
    .compare td.col-trad { font-family: 'Cairo', sans-serif; color: #999; font-weight: 500; font-size: 13px; }
    .compare tr:last-child td, .compare tr:last-child th { border-bottom: none; }
    .check { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: rgba(255,255,255,.25); border-radius: 50%; color: var(--white); font-size: 16px; font-weight: 900; border: 2px solid rgba(255,255,255,.5); }

    /* -- ENGINE -- */
    .engine { padding: 80px 0; background: var(--white); }
    .engine-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
    .engine-card { background: var(--dark); border-radius: 16px; padding: 40px; color: var(--white); }
    .engine-title { font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--green); margin-bottom: 10px; }
    [dir="rtl"] .engine-title { letter-spacing: 0; }
    .engine-name { font-size: 28px; font-weight: 900; color: var(--white); margin-bottom: 12px; }
    .engine-desc { font-size: 14px; color: #aaa; line-height: 1.7; margin-bottom: 28px; padding-bottom: 28px; border-bottom: 1px solid rgba(255,255,255,.1); }
    .engine-feat { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
    .engine-feat:last-child { margin-bottom: 0; }
    .engine-feat-text { flex: 1; }
    .engine-feat-icon { width: 52px; height: 52px; background: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .engine-feat-icon svg { width: 22px; height: 22px; stroke: var(--white); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .engine-feat-title { font-size: 18px; font-weight: 800; color: var(--white); margin-bottom: 4px; }
    .engine-feat-sub { font-size: 13px; color: #888; line-height: 1.5; }
    .engine-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .metric-card { border: 1px solid var(--border); border-radius: 14px; padding: 32px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; }
    .metric-icon { width: 56px; height: 56px; border-radius: 50%; background: rgba(0,106,59,.09); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
    .metric-icon svg { width: 26px; height: 26px; stroke: var(--green); fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
    .metric-val { font-size: 17px; font-weight: 800; color: var(--green); margin-bottom: 6px; }
    .metric-label { font-size: 13px; color: var(--gray-text); font-weight: 500; line-height: 1.5; }

    /* -- DIVISIONS -- */
    .divs { padding: 60px 0; background: var(--cream); }
    .divs-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
    .div-card { background: var(--white); border: 1px solid var(--border); border-radius: 10px; padding: 18px 16px; text-decoration: none; color: inherit; display: block; transition: border-color .2s, box-shadow .2s; }
    .div-card:hover { border-color: var(--green); box-shadow: 0 4px 16px rgba(0,106,59,.08); }
    .div-num { font-size: 11px; font-weight: 700; color: #aaa; letter-spacing: 0.5px; margin-bottom: 4px; }
    .div-name { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
    .div-count { font-size: 12px; color: #888; }

    /* -- NEWS -- */
    .news { padding: 80px 0; background: var(--white); }
    .news-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 12px; }
    .news-top-title { font-size: 16px; font-weight: 700; color: var(--dark); }
    .news-link { font-size: 13px; font-weight: 600; color: var(--green); display: flex; align-items: center; gap: 4px; }
    .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .news-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
    .news-img-placeholder { height: 200px; overflow: hidden; position: relative; }
    .news-img-placeholder img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s ease; }
    .news-card:hover .news-img-placeholder img { transform: scale(1.05); }
    .news-body { padding: 20px; }
    .news-tag { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
    [dir="rtl"] .news-tag { letter-spacing: 0; }
    .news-tag.market { color: #e07b00; }
    .news-tag.tech { color: var(--green); }
    .news-tag.case { color: #5555cc; }
    .news-title { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .news-desc { font-size: 13px; color: var(--gray-text); line-height: 1.65; }

    /* -- BRAND CTA -- */
    .brand { padding: 80px 0; background: var(--dark); }
    .brand-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: start; max-width: 1100px; margin: 0 auto; padding: 0 32px; }
    .brand-left h3 { font-family: 'Cairo', sans-serif; font-size: 38px; font-weight: 900; color: var(--white); margin-bottom: 16px; line-height: 1.15; }
    .brand-left p { font-size: 13px; color: #999; margin-bottom: 32px; max-width: 400px; line-height: 1.7; }
    .listing-row { display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: 20px 24px; margin-bottom: 12px; font-size: 14px; font-weight: 600; color: var(--white); }
    .listing-badge { font-size: 13px; font-weight: 700; color: #aaa; }
    .listing-badge.free { color: var(--green); }
    .listing-badge.sales { color: var(--white); font-weight: 700; }
    .listing-row.active { border-color: var(--green); background: rgba(0,106,59,.08); }
    .brand-right { background: #1a1a1a; border: 1px solid rgba(255,255,255,.07); border-radius: 20px; padding: 44px; }
    .brand-right h4 { font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 800; color: var(--white); margin-bottom: 24px; }
    .brand-benefit { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; font-size: 13px; color: #bbb; line-height: 1.6; }
    .brand-benefit::before { content: ""; display: flex; align-items: center; justify-content: center; min-width: 20px; width: 20px; height: 20px; background: var(--green); border-radius: 50%; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: center; }

    /* -- CTA BANNER -- */
    .cta-banner { padding: 80px 0; background: var(--white); text-align: center; }
    .cta-banner h2 { font-size: 40px; font-weight: 900; color: var(--dark); margin-bottom: 12px; letter-spacing: -1px; }
    [dir="rtl"] .cta-banner h2 { letter-spacing: 0; }
    .cta-banner p { font-size: 14px; color: var(--gray-text); margin-bottom: 30px; line-height: 1.65; }

    /* -- FAQ -- */
    .faq { padding: 80px 0; background: var(--cream); }
    .faq-inner { max-width: 680px; margin: 0 auto; }
    .faq-title { font-size: 25px; font-weight: 700; color: var(--dark); text-align: center; margin-bottom: 36px; }
    .faq-item { border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; background: var(--white); overflow: hidden; }
    .faq-q { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--dark); user-select: none; gap: 12px; }
    .faq-q svg { flex-shrink: 0; transition: transform .25s; color: #888; }
    .faq-item.open .faq-q svg { transform: rotate(180deg); }
    .faq-a { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
    .faq-item.open .faq-a { max-height: 300px; }
    .faq-a-inner { padding: 0 22px 18px; font-size: 14px; color: var(--gray-text); line-height: 1.7; }

    /* -- RESPONSIVE (page-specific) -- */
    @media (max-width: 1024px) {
        .hero { padding: 80px 0 70px; }
        .hero-inner { grid-template-columns: 1fr; gap: 42px; }
        .hero-content { max-width: 100%; }
        .hero h1 { font-size: 52px; }
        .hero-sub { max-width: 620px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .divs-grid { grid-template-columns: repeat(3, 1fr); }
        .how-grid { grid-template-columns: repeat(2, 1fr); }
        .engine-inner { grid-template-columns: 1fr; }
        .engine-metrics { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 768px) {
        .hero { padding: 58px 0 50px; }
        .hero-inner { grid-template-columns: 1fr; gap: 30px; }
        .hero h1 { font-size: 40px; line-height: 1.08; }
        [dir="rtl"] .hero h1 { letter-spacing: 0; }
        .hero-sub { max-width: 100%; font-size: 14px; line-height: 1.45; margin-bottom: 26px; }
        .hero-mockup { display: none; }
        .problem-grid { grid-template-columns: 1fr; }
        .how-grid { grid-template-columns: 1fr; }
        .pillars-grid { grid-template-columns: 1fr; }
        .eco-grid { grid-template-columns: 1fr; }
        .engine-inner { grid-template-columns: 1fr; }
        .engine-metrics { grid-template-columns: repeat(2, 1fr); }
        .brand-inner { grid-template-columns: 1fr; gap: 32px; padding: 0 20px; }
        .news-grid { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .divs-grid { grid-template-columns: repeat(2, 1fr); }
        .hero-btns { flex-direction: column; }
        .hero-btns .btn { width: 100%; justify-content: center; }
        .hero-btns .btn-lg { min-height: 56px; font-size: 15px; }
        .cta-banner h2 { font-size: 28px; }
        .brand-left h3 { font-size: 26px; }
        .section-title { font-size: 18px; }
    }
    @media (max-width: 480px) {
        .container { padding: 0 16px; }
        .stats-grid { grid-template-columns: 1fr; }
        .divs-grid { grid-template-columns: repeat(2, 1fr); }
        .engine-metrics { grid-template-columns: 1fr 1fr; }
        .stat-value { font-size: 42px; }
        .metric-val { font-size: 30px; }
        .hero h1 { font-size: 34px; }
        .hero-tag { font-size: 13px; margin-bottom: 16px; }
        .hero-sub { font-size: 13.5px; }
        /* Comparison table: tighten cell padding on small screens */
        .compare th, .compare td { padding: 14px 12px; font-size: 13px; }
        .compare { padding: 48px 0; }
        /* Scroll hint shadow on right edge */
        .compare-wrap { position: relative; box-shadow: inset -6px 0 8px -4px rgba(0,0,0,.06); }
    }
</style>
@endsection

@push('schema')
@php
$_homeSchema = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'           => 'Organization',
            '@id'             => 'https://www.qimta.com/#organization',
            'name'            => 'Qimta Technology Company',
            'alternateName'   => ['كيمتا', 'Qimta'],
            'url'             => 'https://www.qimta.com',
            'description'     => 'Qimta is a B2B construction pricing platform that retrieves verified pricing for ' . number_format($catalogStats['products']) . ' products via a RAG engine in under 60 seconds. Free for buyers. Deployed across Saudi Arabia and the GCC.',
            'foundingDate'    => '2024',
            'foundingLocation'=> [
                '@type'          => 'Place',
                'name'           => 'Riyadh, Saudi Arabia',
                'addressCountry' => 'SA',
            ],
            'areaServed' => [
                ['@type' => 'Country', 'name' => 'Saudi Arabia'],
                ['@type' => 'Country', 'name' => 'United Arab Emirates'],
                ['@type' => 'Country', 'name' => 'Qatar'],
                ['@type' => 'Country', 'name' => 'Kuwait'],
                ['@type' => 'Country', 'name' => 'Bahrain'],
                ['@type' => 'Country', 'name' => 'Oman'],
            ],
            'sameAs' => [
                'https://www.linkedin.com/company/qimta/',
                'https://www.youtube.com/@Qimtatech',
                'https://x.com/QimtaSm',
            ],
        ],
        [
            '@type'           => 'WebSite',
            '@id'             => 'https://www.qimta.com/#website',
            'url'             => 'https://www.qimta.com',
            'name'            => 'Qimta Technology Company',
            'publisher'       => ['@id' => 'https://www.qimta.com/#organization'],
            'inLanguage'      => ['en', 'ar'],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => 'https://www.qimta.com/catalog?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$_faqSchema = json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        ['@type'=>'Question','name'=>__('welcome.faq.q1'),'acceptedAnswer'=>['@type'=>'Answer','text'=>__('welcome.faq.a1')]],
        ['@type'=>'Question','name'=>__('welcome.faq.q2'),'acceptedAnswer'=>['@type'=>'Answer','text'=>__('welcome.faq.a2')]],
        ['@type'=>'Question','name'=>__('welcome.faq.q3'),'acceptedAnswer'=>['@type'=>'Answer','text'=>__('welcome.faq.a3')]],
        ['@type'=>'Question','name'=>__('welcome.faq.q4'),'acceptedAnswer'=>['@type'=>'Answer','text'=>__('welcome.faq.a4')]],
        ['@type'=>'Question','name'=>__('welcome.faq.q5'),'acceptedAnswer'=>['@type'=>'Answer','text'=>__('welcome.faq.a5')]],
        ['@type'=>'Question','name'=>__('welcome.faq.q6'),'acceptedAnswer'=>['@type'=>'Answer','text'=>__('welcome.faq.a6', ['products' => number_format($catalogStats['products'])])]],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_homeSchema !!}</script>
<script type="application/ld+json">{!! $_faqSchema !!}</script>
@endpush

@section('content')

{{-- GEO Fact Block � machine-readable for LLMs/AI overviews --}}
<div class="container">
<p id="fact-block" style="font-size:13px;color:#777;line-height:1.75;border-left:3px solid #006a3b;padding:10px 16px;background:#f9fdf9;border-radius:0 8px 8px 0;margin:0 0 0 0;" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@if(app()->getLocale() === 'ar')
    شركة كيمتا للتكنولوجيا منصة تسعير إنشائية B2B تفهرس {{ number_format($catalogStats['products']) }} منتجاً معتمداً في السعودية ودول الخليج. يسترجع محرك RAG أسعار بنود جدول الكميات خلال أقل من 60 ثانية بدقة 99.9% بالمقارنة مع أكثر من مليار مواصفة تقنية للمصنّعين. التسعير مجاني لمشتري مواد البناء وفرق المشتريات.
@else
    Qimta Technology Company is a B2B construction pricing platform indexing {{ number_format($catalogStats['products']) }} verified products across Saudi Arabia and GCC. The RAG matching engine retrieves BOQ line-item prices in under 60 seconds with 99.9% accuracy by cross-referencing 1B+ manufacturer technical specifications. Pricing is free for construction buyers and procurement teams.
@endif
</p>
</div>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
                <div class="hero-content">
                    <h2 class="hero-tag">{{ __('welcome.hero.tag') }}</h2>

                    <h1>{{ __('welcome.hero.h1') }}</h1>

                    <p class="hero-sub">
                        {{ __('welcome.hero.sub', ['products' => number_format($catalogStats['products'])]) }}
                    </p>

                    <div class="hero-btns">
                        <a href="{{ route('enduser.login') }}" class="btn btn-dark btn-lg">
                            {{ __('welcome.hero.btn_primary') }}
                        </a>

                        <a href="{{ route('contact') }}" class="btn btn-outline btn-lg">
                            {{ __('welcome.hero.btn_secondary') }}
                        </a>
                    </div>
                </div>

                <div class="hero-mockup">
                    <div class="mock-header">
                        <span>Construction Pricing</span>
                        <span class="mock-badge">{{ __('welcome.hero.mock_live') }}</span>
                    </div>

                    <div class="mock-row"><span>Insulation</span><span>3,952</span><span>4,200</span></div>
                    <div class="mock-row"><span>Steel Frame</span><span>1,230</span><span>1,860</span></div>
                    <div class="mock-row"><span>Raised Access</span><span>3,900</span><span>1,035</span></div>

                    <div class="mock-actions">
                        <span class="mock-btn">{{ __('welcome.hero.mock_match') }}</span>
                        <span class="mock-btn-alt">{{ __('welcome.hero.mock_export') }}</span>
                    </div>

                    <div class="mock-row"><span>Tables</span><span>11,000</span><span>+47,820</span></div>
                    <div class="mock-row"><span>Outdoor Chairs</span><span>3,120</span><span>11,620</span></div>
                    <div class="mock-row"><span>Glazing</span><span>3,950</span><span>3,800</span></div>
                    <div class="mock-row"><span>Ground Branches</span><span>24,120</span><span>+</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="stats">
        <div class="container">
            <div class="stats-top">
                <div>
                    <p class="stats-label">{{ __('welcome.stats.label') }}</p>
                    <p class="stats-sub">{{ __('welcome.stats.sub') }}</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="stats-link">{{ __('welcome.stats.link') }} &rarr;</a>
            </div>
            <div class="stats-grid">
                <div class="stat-card" itemscope itemtype="https://schema.org/QuantitativeValue">
                    <div class="stat-icon">&#9783;</div>
                    <p class="stat-label" itemprop="name">{{ __('welcome.stats.products') }}</p>
                    <div class="stat-value" itemprop="value" content="{{ $catalogStats['products'] }}">{{ number_format($catalogStats['products']) }}</div>
                    <div class="stat-line"></div>
                </div>
                <div class="stat-card" itemscope itemtype="https://schema.org/QuantitativeValue">
                    <div class="stat-icon">&#10010;</div>
                    <p class="stat-label" itemprop="name">{{ __('welcome.stats.specs') }}</p>
                    <div class="stat-value" itemprop="value" content="1000000000">1B+</div>
                    <div class="stat-line"></div>
                </div>
                <div class="stat-card" itemscope itemtype="https://schema.org/QuantitativeValue">
                    <div class="stat-icon">&#10004;</div>
                    <p class="stat-label" itemprop="name">{{ __('welcome.stats.brands') }}</p>
                    <div class="stat-value" itemprop="value" content="100">100%</div>
                    <div class="stat-line"></div>
                </div>
                <div class="stat-card" itemscope itemtype="https://schema.org/QuantitativeValue">
                    <div class="stat-icon">&#36;</div>
                    <p class="stat-label" itemprop="name">{{ __('welcome.stats.cost') }}</p>
                    <div class="stat-value" itemprop="value" content="0">{{ $isAr ? 'مجاني' : 'FREE' }}</div>
                    <div class="stat-line"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROBLEM -->
    <section class="problem">
        <div class="container">
            <div class="section-intro">
                <strong>{{ __('welcome.problem.headline') }}</strong>
                {{ __('welcome.problem.sub') }}
            </div>
            <div class="problem-grid">
                <div class="problem-card">
                    <div class="problem-icon">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <p class="problem-title">{{ __('welcome.problem.p1_title') }}</p>
                    <p class="problem-desc">{{ __('welcome.problem.p1_desc') }}</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <p class="problem-title">{{ __('welcome.problem.p2_title') }}</p>
                    <p class="problem-desc">{{ __('welcome.problem.p2_desc') }}</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <p class="problem-title">{{ __('welcome.problem.p3_title') }}</p>
                    <p class="problem-desc">{{ __('welcome.problem.p3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how">
        <div class="container">
            <p class="section-title">{{ __('welcome.how.title') }}</p>
            <div class="how-grid">
                <div class="how-card">
                    <div class="how-icon">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
                    </div>
                    <p class="how-num">01</p>
                    <p class="how-title">{{ __('welcome.how.s1_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s1_desc') }}</p>
                </div>
                <div class="how-card active">
                    <div class="how-icon">
                        <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </div>
                    <p class="how-num">02</p>
                    <p class="how-title">{{ __('welcome.how.s2_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s2_desc') }}</p>
                </div>
                <div class="how-card">
                    <div class="how-icon">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <p class="how-num">03</p>
                    <p class="how-title">{{ __('welcome.how.s3_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s3_desc') }}</p>
                </div>
                <div class="how-card">
                    <div class="how-icon">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    </div>
                    <p class="how-num">04</p>
                    <p class="how-title">{{ __('welcome.how.s4_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s4_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PILLARS -->
    <section class="pillars">
        <div class="container">
            <div class="pillars-grid">
                <div class="pillar">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <p class="pillar-num">{{ __('welcome.pillars.p1_num') }}</p>
                    <p class="pillar-title">{{ __('welcome.pillars.p1_title') }}</p>
                    <p class="pillar-desc">{{ __('welcome.pillars.p1_desc') }}</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><path d="M16 6l2 2-2 2" stroke-width="1.2"/></svg>
                    </div>
                    <p class="pillar-num">{{ __('welcome.pillars.p2_num') }}</p>
                    <p class="pillar-title">{{ __('welcome.pillars.p2_title') }}</p>
                    <p class="pillar-desc">{{ __('welcome.pillars.p2_desc') }}</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    </div>
                    <p class="pillar-num">{{ __('welcome.pillars.p3_num') }}</p>
                    <p class="pillar-title">{{ __('welcome.pillars.p3_title') }}</p>
                    <p class="pillar-desc">{{ __('welcome.pillars.p3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ECOSYSTEM -->
    <section class="eco">
        <div class="container">
            <div class="eco-header">
                <p class="eco-label">{{ __('welcome.eco.label') }}</p>
                <p class="eco-sub">{{ __('welcome.eco.sub') }}</p>
            </div>
            <div class="eco-grid">
                <div class="eco-card">
                    <div class="eco-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <p class="eco-title">{{ __('welcome.eco.contractors_title') }}</p>
                    <p class="eco-desc">{{ __('welcome.eco.contractors_desc') }}</p>
                    <a href="{{ route('enduser.register') }}" class="eco-link">{{ __('welcome.eco.learn_more') }} &rarr;</a>
                </div>
                <div class="eco-card">
                    <div class="eco-icon">
                        <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <p class="eco-title">{{ __('welcome.eco.procurement_title') }}</p>
                    <p class="eco-desc">{{ __('welcome.eco.procurement_desc') }}</p>
                    <a href="{{ route('catalog.index') }}" class="eco-link">{{ __('welcome.eco.learn_more') }} &rarr;</a>
                </div>
                <div class="eco-card">
                    <div class="eco-icon">
                        <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    </div>
                    <p class="eco-title">{{ __('welcome.eco.brands_title') }}</p>
                    <p class="eco-desc">{{ __('welcome.eco.brands_desc') }}</p>
                    <a href="{{ route('for-brands') }}" class="eco-link">{{ __('welcome.eco.learn_more') }} &rarr;</a>
                </div>
            </div>
        </div>
    </section>

    <!-- COMPARISON TABLE -->
    <section class="compare">
        <div class="container">
            <div class="compare-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('welcome.compare.feature') }}</th>
                        <th class="col-qimta">{{ __('welcome.compare.qimta_engine') }}</th>
                        <th class="col-trad">{{ __('welcome.compare.traditional') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('welcome.compare.speed_label') }}</td>
                        <td class="col-qimta">{{ __('welcome.compare.speed_qimta') }}</td>
                        <td class="col-trad">{{ __('welcome.compare.speed_trad') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('welcome.compare.valid_label') }}</td>
                        <td class="col-qimta">{{ __('welcome.compare.valid_qimta') }}</td>
                        <td class="col-trad">{{ __('welcome.compare.valid_trad') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('welcome.compare.brand_label') }}</td>
                        <td class="col-qimta">{{ __('welcome.compare.brand_qimta') }}</td>
                        <td class="col-trad">{{ __('welcome.compare.brand_trad') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('welcome.compare.data_label') }}</td>
                        <td class="col-qimta">{{ __('welcome.compare.data_qimta') }}</td>
                        <td class="col-trad">{{ __('welcome.compare.data_trad') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('welcome.compare.buy_label') }}</td>
                        <td class="col-qimta">{{ __('welcome.compare.buy_qimta') }}</td>
                        <td class="col-trad">{{ __('welcome.compare.buy_trad') }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </section>

    <!-- ENGINE -->
    <section class="engine">
        <div class="container engine-inner">
            <div class="engine-card">
                <p class="engine-title">{{ __('welcome.engine.label') }}</p>
                <p class="engine-name">{{ __('welcome.engine.name') }}</p>
                <p class="engine-desc">{{ __('welcome.engine.desc') }}</p>
                <div class="engine-feat">
                    <div class="engine-feat-text">
                        <p class="engine-feat-title">{{ __('welcome.engine.feat1_title') }}</p>
                        <p class="engine-feat-sub">{{ __('welcome.engine.feat1_sub') }}</p>
                    </div>
                    <div class="engine-feat-icon">
                        <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>
                    </div>
                </div>
                <div class="engine-feat">
                    <div class="engine-feat-text">
                        <p class="engine-feat-title">{{ __('welcome.engine.feat2_title') }}</p>
                        <p class="engine-feat-sub">{{ __('welcome.engine.feat2_sub') }}</p>
                    </div>
                    <div class="engine-feat-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
            </div>
            <div class="engine-metrics">
                <div class="metric-card">
                    <div class="metric-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                    </div>
                    <p class="metric-val">{{ number_format($catalogStats['products']) }}</p>
                    <p class="metric-label">{{ __('welcome.engine.m1_desc') }}</p>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <p class="metric-val">{{ __('welcome.engine.m2_title') }}</p>
                    <p class="metric-label">{{ __('welcome.engine.m2_desc') }}</p>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </div>
                    <p class="metric-val">{{ __('welcome.engine.m3_title') }}</p>
                    <p class="metric-label">{{ __('welcome.engine.m3_desc') }}</p>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <svg viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    </div>
                    <p class="metric-val">{{ __('welcome.engine.m4_title') }}</p>
                    <p class="metric-label">{{ __('welcome.engine.m4_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DIVISIONS -->
    <section class="divs">
        <div class="container">
            <div class="divs-grid">
                @forelse($divisions ?? [] as $div)
                    <a href="{{ route('catalog.division', $div->slug) }}" class="div-card">
                        <p class="div-num">{{ strtoupper(preg_replace('/[^0-9]/', '', $div->name) ? 'Div ' . preg_replace('/[^0-9]/', '', $div->name) : '') }}</p>
                        <p class="div-name">{{ $div->name }}</p>
                        <p class="div-count">{{ number_format($div->products) }} {{ __('welcome.divs.products') }}</p>
                    </a>
                @empty
                    {{-- fallback hardcoded cards if DB unavailable --}}
                    <div class="div-card"><p class="div-num">Div 03</p><p class="div-name">{{ $isAr ? 'خرسانة' : 'Concrete' }}</p><p class="div-count">12,402 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 04</p><p class="div-name">{{ $isAr ? 'بناء' : 'Masonry' }}</p><p class="div-count">8,190 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 05</p><p class="div-name">{{ $isAr ? 'معادن' : 'Metals' }}</p><p class="div-count">26,561 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 07</p><p class="div-name">{{ $isAr ? 'عزل حراري ومائي' : 'Thermal & Moisture' }}</p><p class="div-count">15,003 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 08</p><p class="div-name">{{ $isAr ? 'فتحات' : 'Openings' }}</p><p class="div-count">53,291 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 09</p><p class="div-name">{{ $isAr ? 'تشطيبات' : 'Finishes' }}</p><p class="div-count">42,891 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 21</p><p class="div-name">{{ $isAr ? 'إطفاء الحريق' : 'Fire Suppression' }}</p><p class="div-count">5,620 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 22</p><p class="div-name">{{ $isAr ? 'سباكة' : 'Plumbing' }}</p><p class="div-count">28,109 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 23</p><p class="div-name">{{ $isAr ? 'تكييف' : 'HVAC' }}</p><p class="div-count">31,005 {{ __('welcome.divs.products') }}</p></div>
                    <div class="div-card"><p class="div-num">Div 26</p><p class="div-name">{{ $isAr ? 'كهرباء' : 'Electrical' }}</p><p class="div-count">55,420 {{ __('welcome.divs.products') }}</p></div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- NEWS -->
    <section class="news">
        <div class="container">
            <div class="news-top">
                <p class="news-top-title">{{ __('welcome.news.title') }}</p>
                <a href="{{ route('news') }}" class="news-link">{{ __('welcome.news.view_all') }} &#8599;</a>
            </div>
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-img-placeholder">
                        <img src="{{ asset('images/news/construction-materials.jpg') }}"
                             width="800" height="534"
                             alt="{{ $isAr ? 'أسعار مواد البناء في السعودية - مشروع إنشائي بالسعودية' : 'Construction materials pricing in Saudi Arabia - local building project' }}"
                             loading="lazy">
                    </div>
                    <div class="news-body">
                        <p class="news-tag market">{{ __('welcome.news.tag_market') }}</p>
                        <p class="news-title">{{ __('welcome.news.n1_title') }}</p>
                        <p class="news-desc">{{ __('welcome.news.n1_desc') }}</p>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-img-placeholder">
                        <img src="{{ asset('images/news/smart-pricing-tech.jpg') }}"
                             width="800" height="534"
                             alt="{{ $isAr ? 'تقنية التسعير الذكي لمواد البناء - منصة كيمتا للإنشاء' : 'Smart construction pricing technology powered by QIMTA platform' }}"
                             loading="lazy">
                    </div>
                    <div class="news-body">
                        <p class="news-tag tech">{{ __('welcome.news.tag_tech') }}</p>
                        <p class="news-title">{{ __('welcome.news.n2_title') }}</p>
                        <p class="news-desc">{{ __('welcome.news.n2_desc') }}</p>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-img-placeholder">
                        <img src="{{ asset('images/news/business-meeting.jpg') }}"
                             width="800" height="534"
                             alt="{{ $isAr ? 'نجاح عملاء كيمتا - مقاول يوفر تكاليف المشاريع الإنشائية' : 'QIMTA client success - contractor reducing construction project costs' }}"
                             loading="lazy">
                    </div>
                    <div class="news-body">
                        <p class="news-tag case">{{ __('welcome.news.tag_case') }}</p>
                        <p class="news-title">{{ __('welcome.news.n3_title') }}</p>
                        <p class="news-desc">{{ __('welcome.news.n3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BRAND CTA -->
    <section class="brand">
        <div class="brand-inner">
            <div class="brand-left">
                <h2>{{ __('welcome.brand.h3') }}</h2>
                <p>{{ __('welcome.brand.sub') }}</p>
                <div class="listing-row">
                    <span>{{ __('welcome.brand.basic') }}</span>
                    <span class="listing-badge free">{{ __('welcome.brand.free') }}</span>
                </div>
                <div class="listing-row active">
                    <span>{{ __('welcome.brand.exclusive') }}</span>
                    <span class="listing-badge sales">{{ __('welcome.brand.contact_sales') }}</span>
                </div>
            </div>
            <div class="brand-right">
                <h3>{{ __('welcome.brand.benefits_h') }}</h3>
                <div class="brand-benefit">{{ __('welcome.brand.benefit1') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit2') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit3') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit4') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit5') }}</div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="cta-banner">
        <div class="container">
            <h2>{{ __('welcome.cta.h2') }}</h2>
            <p>{{ __('welcome.cta.sub') }}</p>
            <a href="{{ route('enduser.login') }}" class="btn btn-primary btn-lg">{{ __('welcome.cta.btn') }}</a>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq">
        <div class="container">
            <div class="faq-inner">
                <h2 class="faq-title">{{ __('welcome.faq.title') }}</h2>
                @foreach(range(1, 6) as $i)
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        {{ __("welcome.faq.q{$i}") }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="faq-a"><div class="faq-a-inner">{{ $i === 6 ? __("welcome.faq.a6", ['products' => number_format($catalogStats['products'])]) : __("welcome.faq.a{$i}") }}</div></div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection

@section('scripts')
<script>
    function toggleFaq(el) {
        var item = el.closest('.faq-item');
        var isOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(function(i) { i.classList.remove('open'); });
        if (!isOpen) item.classList.add('open');
    }
</script>
@endsection
