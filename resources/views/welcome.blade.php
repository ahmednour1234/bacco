@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title')QIMTA &mdash; {{ __('welcome.hero.tag') }}@endsection

@section('nav-cta')
    <a href="#" class="btn-nav-cta">
        {{ __('welcome.nav.price_boq') }}
        <span class="cta-badge">{{ $isAr ? 'مجاني' : 'FREE' }}</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
@endsection

@section('mobile-cta')
    <a href="#" class="btn btn-primary">{{ __('welcome.nav.price_boq') }}</a>
@endsection

@section('styles')
<style>
    /* ── HERO ── */
    .hero { padding: 90px 0 80px; background: var(--white); }
    .hero-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
    .hero-tag { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 16px; font-weight: 800; letter-spacing: 4px; color: var(--green); text-transform: uppercase; margin-bottom: 20px; line-height: 24px; }
    [dir="rtl"] .hero-tag { letter-spacing: 0; }
    .hero h1 { font-size: 56px; font-weight: 900; line-height: 1.05; letter-spacing: -2px; margin-bottom: 22px; color: var(--dark); }
    [dir="rtl"] .hero h1 { letter-spacing: 0; }
    .hero-sub { font-size: 16px; color: var(--gray-text); max-width: 460px; margin-bottom: 36px; line-height: 1.7; }
    .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; }
    .hero-mockup { background: #d8e8e0; border-radius: 16px; padding: 28px; min-height: 340px; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 20px 60px rgba(0,0,0,.12); }
    .mock-header { background: var(--white); border-radius: 8px; padding: 12px 16px; font-size: 13px; font-weight: 600; color: #333; display: flex; align-items: center; justify-content: space-between; }
    .mock-badge { background: var(--green-btn); color: var(--white); font-size: 10px; padding: 3px 8px; border-radius: 20px; font-weight: 700; }
    .mock-row { background: var(--white); border-radius: 6px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #555; }
    .mock-row span:last-child { font-weight: 700; color: var(--green); }
    .mock-actions { display: flex; gap: 8px; }
    .mock-btn { background: #f0a800; color: var(--white); padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 700; }
    .mock-btn-alt { background: #e0e0e0; color: #555; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 700; }

    /* ── STATS ── */
    .stats { background: rgba(0, 134, 76, 0.30); padding: 64px 0; }
    .stats-label { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 16px; font-weight: 800; letter-spacing: 4px; color: var(--green); text-transform: uppercase; margin-bottom: 6px; line-height: 24px; }
    [dir="rtl"] .stats-label { letter-spacing: 0; }
    .stats-sub { font-size: 13px; color: #444; margin-bottom: 0; }
    .stats-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
    .stats-link { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 13px; font-weight: 700; color: var(--green); display: flex; align-items: center; gap: 6px; margin-top: 4px; white-space: nowrap; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card { background: var(--white); border-radius: 12px; padding: 28px 24px; }
    .stat-icon { font-size: 20px; margin-bottom: 12px; color: #555; }
    .stat-label { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: #888; margin-bottom: 10px; }
    [dir="rtl"] .stat-label { letter-spacing: 0; }
    .stat-value { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 42px; font-weight: 800; color: var(--dark); letter-spacing: -2px; line-height: 1; }
    [dir="rtl"] .stat-value { letter-spacing: 0; }
    .stat-line { width: 36px; height: 3px; background: var(--green); margin-top: 14px; border-radius: 2px; }

    /* ── PROBLEM ── */
    .problem { padding: 80px 0; background: var(--white); }
    .section-intro { font-size: 15px; color: #333; max-width: 520px; margin-bottom: 48px; }
    .section-intro strong { display: block; font-size: 18px; font-weight: 700; margin-bottom: 6px; color: var(--dark); }
    .problem-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .problem-card { border: 1px solid var(--border); border-radius: 12px; padding: 28px 24px; }
    .problem-icon { font-size: 22px; color: #888; margin-bottom: 14px; }
    .problem-title { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .problem-desc { font-size: 14px; color: var(--gray-text); line-height: 1.65; }

    /* ── HOW IT WORKS ── */
    .how { padding: 80px 0; background: var(--white); }
    .section-title { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 22px; font-weight: 800; color: var(--dark); text-align: left; margin-bottom: 40px; }
    [dir="rtl"] .section-title { text-align: right; }
    .how-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; align-items: stretch; }
    .how-card { background: var(--white); border-radius: 16px; padding: 32px 24px; border: 1px solid var(--border); display: flex; flex-direction: column; position: relative; overflow: hidden; }
    .how-card.active { background: var(--green); color: var(--white); border-color: transparent; }
    .how-card.active::after { content: '\26A1'; position: absolute; bottom: -10px; right: -4px; font-size: 120px; opacity: 0.12; line-height: 1; color: #f0a800; pointer-events: none; }
    [dir="rtl"] .how-card.active::after { right: auto; left: -4px; }
    .how-num { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 1px; color: #bbb; margin-bottom: 16px; }
    .how-card.active .how-num { color: rgba(255,255,255,.6); }
    .how-title { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 16px; font-weight: 800; margin-bottom: 10px; color: var(--dark); }
    .how-card.active .how-title { color: var(--white); }
    .how-desc { font-size: 14px; color: var(--gray-text); line-height: 1.65; }
    .how-card.active .how-desc { color: rgba(255,255,255,.85); }
    .how-icon { font-size: 28px; margin-bottom: 20px; display: block; }

    /* ── PILLARS ── */
    .pillars { background: var(--green); padding: 40px 0; }
    .pillars-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: rgba(255,255,255,.15); }
    .pillar { padding: 36px 32px; background: var(--green); }
    .pillar-num { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: rgba(255,255,255,.6); text-transform: uppercase; margin-bottom: 8px; }
    [dir="rtl"] .pillar-num { letter-spacing: 0; }
    .pillar-title { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 15px; font-weight: 800; color: var(--white); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
    [dir="rtl"] .pillar-title { letter-spacing: 0; }
    .pillar-desc { font-size: 13px; color: rgba(255,255,255,.75); }

    /* ── ECOSYSTEM ── */
    .eco { padding: 80px 0; background: var(--white); }
    .eco-label { font-size: 14px; font-weight: 600; color: #555; margin-bottom: 32px; }
    .eco-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .eco-card { border: 1px solid var(--border); border-radius: 12px; padding: 32px 28px; }
    .eco-title { font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .eco-desc { font-size: 14px; color: var(--gray-text); margin-bottom: 20px; line-height: 1.65; }
    .eco-link { font-size: 13px; font-weight: 600; color: var(--green); display: inline-flex; align-items: center; gap: 6px; }
    .eco-link:hover { text-decoration: underline; }

    /* ── COMPARISON TABLE ── */
    .compare { padding: 80px 0; background: var(--cream); }
    .compare-wrap { border-radius: 16px; padding: 8px; background: var(--white); overflow-x: auto; }
    .compare table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 10px; overflow: hidden; min-width: 540px; }
    .compare th, .compare td { padding: 18px 28px; text-align: left; font-size: 14px; border-bottom: 1px solid var(--border); }
    [dir="rtl"] .compare th, [dir="rtl"] .compare td { text-align: right; }
    .compare th { font-size: 13px; font-weight: 600; color: #aaa; background: var(--white); }
    .compare th.col-qimta { background: var(--green); color: var(--white); text-align: center; font-family: 'Manrope', 'Cairo', sans-serif; font-weight: 800; font-size: 15px; }
    .compare td { font-size: 14px; font-weight: 700; color: var(--dark); }
    .compare td.col-qimta { background: var(--green); text-align: center; font-weight: 700; color: var(--white); border-bottom: 1px solid rgba(255,255,255,.15); }
    .compare td.col-trad { color: #aaa; font-weight: 400; }
    .compare tr:last-child td, .compare tr:last-child th { border-bottom: none; }
    .check { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: var(--green); border-radius: 50%; color: var(--white); font-size: 14px; font-weight: 700; }

    /* ── ENGINE ── */
    .engine { padding: 80px 0; background: var(--white); }
    .engine-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
    .engine-card { background: var(--dark); border-radius: 16px; padding: 36px; color: var(--white); }
    .engine-title { font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--green); margin-bottom: 8px; }
    [dir="rtl"] .engine-title { letter-spacing: 0; }
    .engine-name { font-size: 22px; font-weight: 800; color: var(--white); margin-bottom: 14px; }
    .engine-desc { font-size: 14px; color: #aaa; line-height: 1.65; margin-bottom: 28px; }
    .engine-feat { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; }
    .engine-feat-icon { width: 36px; height: 36px; background: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--white); flex-shrink: 0; }
    .engine-feat-title { font-size: 14px; font-weight: 700; color: var(--white); }
    .engine-feat-sub { font-size: 13px; color: #888; }
    .engine-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .metric-card { border: 1px solid var(--border); border-radius: 12px; padding: 28px 24px; text-align: center; }
    .metric-val { font-size: 38px; font-weight: 900; color: var(--green); letter-spacing: -1px; margin-bottom: 6px; }
    [dir="rtl"] .metric-val { letter-spacing: 0; }
    .metric-label { font-size: 14px; color: var(--dark); font-weight: 700; margin-top: 4px; }

    /* ── DIVISIONS ── */
    .divs { padding: 60px 0; background: var(--cream); }
    .divs-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
    .div-card { background: var(--white); border: 1px solid var(--border); border-radius: 10px; padding: 18px 16px; }
    .div-num { font-size: 11px; font-weight: 700; color: #aaa; letter-spacing: 0.5px; margin-bottom: 4px; }
    .div-name { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
    .div-count { font-size: 12px; color: #888; }

    /* ── NEWS ── */
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
    .news-desc { font-size: 13px; color: var(--gray-text); line-height: 1.6; }

    /* ── BRAND CTA ── */
    .brand { padding: 72px 0; background: var(--dark); }
    .brand-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: start; max-width: 1100px; margin: 0 auto; padding: 0 32px; }
    .brand-left h3 { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 32px; font-weight: 800; color: var(--white); margin-bottom: 14px; line-height: 1.2; }
    .brand-left p { font-size: 14px; color: #aaa; margin-bottom: 28px; max-width: 400px; line-height: 1.65; }
    .listing-row { display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,.15); border-radius: 10px; padding: 16px 20px; margin-bottom: 10px; font-size: 14px; color: var(--white); font-weight: 500; }
    .listing-badge { font-size: 13px; font-weight: 700; color: #aaa; }
    .listing-badge.free { color: var(--green); }
    .listing-badge.sales { color: var(--white); font-weight: 700; }
    .listing-row.active { border-color: var(--green); }
    .brand-right { background: #1e1e1e; border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 36px; }
    .brand-right h4 { font-family: 'Manrope', 'Cairo', sans-serif; font-size: 14px; font-weight: 700; color: var(--white); margin-bottom: 20px; }
    .brand-benefit { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; font-size: 14px; color: #ccc; }
    .brand-benefit::before { content: ""; display: flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--green); border-radius: 50%; color: var(--white); font-size: 12px; font-weight: 700; flex-shrink: 0; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: center; }

    /* ── CTA BANNER ── */
    .cta-banner { padding: 80px 0; background: var(--white); text-align: center; }
    .cta-banner h2 { font-size: 36px; font-weight: 900; color: var(--dark); margin-bottom: 12px; letter-spacing: -1px; }
    [dir="rtl"] .cta-banner h2 { letter-spacing: 0; }
    .cta-banner p { font-size: 15px; color: var(--gray-text); margin-bottom: 30px; }

    /* ── FAQ ── */
    .faq { padding: 80px 0; background: var(--cream); }
    .faq-inner { max-width: 680px; margin: 0 auto; }
    .faq-title { font-size: 22px; font-weight: 700; color: var(--dark); text-align: center; margin-bottom: 36px; }
    .faq-item { border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; background: var(--white); overflow: hidden; }
    .faq-q { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--dark); user-select: none; gap: 12px; }
    .faq-q svg { flex-shrink: 0; transition: transform .25s; color: #888; }
    .faq-item.open .faq-q svg { transform: rotate(180deg); }
    .faq-a { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
    .faq-item.open .faq-a { max-height: 300px; }
    .faq-a-inner { padding: 0 22px 18px; font-size: 14px; color: var(--gray-text); line-height: 1.7; }

    /* ── RESPONSIVE (page-specific) ── */
    @media (max-width: 1024px) {
        .hero h1 { font-size: 44px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .divs-grid { grid-template-columns: repeat(3, 1fr); }
        .how-grid { grid-template-columns: repeat(2, 1fr); }
        .engine-inner { grid-template-columns: 1fr; }
        .engine-metrics { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 768px) {
        .hero { padding: 56px 0 48px; }
        .hero-inner { grid-template-columns: 1fr; gap: 32px; }
        .hero h1 { font-size: 36px; letter-spacing: -1px; }
        [dir="rtl"] .hero h1 { letter-spacing: 0; }
        .hero-sub { max-width: 100%; }
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
        .cta-banner h2 { font-size: 28px; }
        .brand-left h3 { font-size: 26px; }
        .section-title { font-size: 18px; }
    }
    @media (max-width: 480px) {
        .container { padding: 0 16px; }
        .stats-grid { grid-template-columns: 1fr; }
        .divs-grid { grid-template-columns: repeat(2, 1fr); }
        .engine-metrics { grid-template-columns: 1fr 1fr; }
        .stat-value { font-size: 34px; }
        .metric-val { font-size: 30px; }
    }
</style>
@endsection

@section('content')

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
            <div>
                <p class="hero-tag">{{ __('welcome.hero.tag') }}</p>
                <h1>{{ __('welcome.hero.h1') }}</h1>
                <p class="hero-sub">{{ __('welcome.hero.sub') }}</p>
                <div class="hero-btns">
                    <a href="#" class="btn btn-dark btn-lg">{{ __('welcome.hero.btn_primary') }}</a>
                    <a href="#" class="btn btn-outline btn-lg">{{ __('welcome.hero.btn_secondary') }}</a>
                </div>
            </div>
            <div class="hero-mockup">
                <div class="mock-header">
                    Construction Pricing <span class="mock-badge">{{ __('welcome.hero.mock_live') }}</span>
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
                <a href="#" class="stats-link">{{ __('welcome.stats.link') }} &rarr;</a>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">&#9783;</div>
                    <p class="stat-label">{{ __('welcome.stats.products') }}</p>
                    <div class="stat-value">418,326</div>
                    <div class="stat-line"></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">&#10010;</div>
                    <p class="stat-label">{{ __('welcome.stats.specs') }}</p>
                    <div class="stat-value">1B+</div>
                    <div class="stat-line"></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">&#10004;</div>
                    <p class="stat-label">{{ __('welcome.stats.brands') }}</p>
                    <div class="stat-value">100%</div>
                    <div class="stat-line"></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">&#36;</div>
                    <p class="stat-label">{{ __('welcome.stats.cost') }}</p>
                    <div class="stat-value">{{ $isAr ? 'مجاني' : 'FREE' }}</div>
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
                    <div class="problem-icon">&#128269;</div>
                    <p class="problem-title">{{ __('welcome.problem.p1_title') }}</p>
                    <p class="problem-desc">{{ __('welcome.problem.p1_desc') }}</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">&#128336;</div>
                    <p class="problem-title">{{ __('welcome.problem.p2_title') }}</p>
                    <p class="problem-desc">{{ __('welcome.problem.p2_desc') }}</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">&#128203;</div>
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
                    <span class="how-icon">&#128196;</span>
                    <p class="how-num">01</p>
                    <p class="how-title">{{ __('welcome.how.s1_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s1_desc') }}</p>
                </div>
                <div class="how-card active">
                    <span class="how-icon" style="color:#f0a800;">&#9889;</span>
                    <p class="how-num">02</p>
                    <p class="how-title">{{ __('welcome.how.s2_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s2_desc') }}</p>
                </div>
                <div class="how-card">
                    <span class="how-icon">&#128269;</span>
                    <p class="how-num">03</p>
                    <p class="how-title">{{ __('welcome.how.s3_title') }}</p>
                    <p class="how-desc">{{ __('welcome.how.s3_desc') }}</p>
                </div>
                <div class="how-card">
                    <span class="how-icon">&#128722;</span>
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
                    <p class="pillar-num">{{ __('welcome.pillars.p1_num') }}</p>
                    <p class="pillar-title">{{ __('welcome.pillars.p1_title') }}</p>
                    <p class="pillar-desc">{{ __('welcome.pillars.p1_desc') }}</p>
                </div>
                <div class="pillar">
                    <p class="pillar-num">{{ __('welcome.pillars.p2_num') }}</p>
                    <p class="pillar-title">{{ __('welcome.pillars.p2_title') }}</p>
                    <p class="pillar-desc">{{ __('welcome.pillars.p2_desc') }}</p>
                </div>
                <div class="pillar">
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
            <p class="eco-label">{{ __('welcome.eco.label') }}</p>
            <div class="eco-grid">
                <div class="eco-card">
                    <p class="eco-title">{{ __('welcome.eco.contractors_title') }}</p>
                    <p class="eco-desc">{{ __('welcome.eco.contractors_desc') }}</p>
                    <a href="#" class="eco-link">{{ __('welcome.eco.learn_more') }} &rarr;</a>
                </div>
                <div class="eco-card">
                    <p class="eco-title">{{ __('welcome.eco.procurement_title') }}</p>
                    <p class="eco-desc">{{ __('welcome.eco.procurement_desc') }}</p>
                    <a href="#" class="eco-link">{{ __('welcome.eco.learn_more') }} &rarr;</a>
                </div>
                <div class="eco-card">
                    <p class="eco-title">{{ __('welcome.eco.brands_title') }}</p>
                    <p class="eco-desc">{{ __('welcome.eco.brands_desc') }}</p>
                    <a href="#" class="eco-link">{{ __('welcome.eco.learn_more') }} &rarr;</a>
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
                        <td class="col-qimta"><span class="check">&#10003;</span></td>
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
                    <div class="engine-feat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg></div>
                    <div>
                        <p class="engine-feat-title">{{ __('welcome.engine.feat1_title') }}</p>
                        <p class="engine-feat-sub">{{ __('welcome.engine.feat1_sub') }}</p>
                    </div>
                </div>
                <div class="engine-feat">
                    <div class="engine-feat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg></div>
                    <div>
                        <p class="engine-feat-title">{{ __('welcome.engine.feat2_title') }}</p>
                        <p class="engine-feat-sub">{{ __('welcome.engine.feat2_sub') }}</p>
                    </div>
                </div>
            </div>
            <div class="engine-metrics">
                <div class="metric-card">
                    <div class="metric-val">418k</div>
                    <p class="metric-label">{{ __('welcome.engine.products') }}</p>
                </div>
                <div class="metric-card">
                    <div class="metric-val">99%</div>
                    <p class="metric-label">{{ __('welcome.engine.match_rate') }}</p>
                </div>
                <div class="metric-card">
                    <div class="metric-val">24/7</div>
                    <p class="metric-label">{{ __('welcome.engine.uptime') }}</p>
                </div>
                <div class="metric-card">
                    <div class="metric-val">&lt; 1s</div>
                    <p class="metric-label">{{ __('welcome.engine.latency') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DIVISIONS -->
    <section class="divs">
        <div class="container">
            <div class="divs-grid">
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
            </div>
        </div>
    </section>

    <!-- NEWS -->
    <section class="news">
        <div class="container">
            <div class="news-top">
                <p class="news-top-title">{{ __('welcome.news.title') }}</p>
                <a href="#" class="news-link">{{ __('welcome.news.view_all') }} &#8599;</a>
            </div>
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-img-placeholder">
                        <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=700&q=80&auto=format&fit=crop" alt="Construction site" loading="lazy">
                    </div>
                    <div class="news-body">
                        <p class="news-tag market">{{ __('welcome.news.tag_market') }}</p>
                        <p class="news-title">{{ __('welcome.news.n1_title') }}</p>
                        <p class="news-desc">{{ __('welcome.news.n1_desc') }}</p>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-img-placeholder">
                        <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=700&q=80&auto=format&fit=crop" alt="Technology circuit board" loading="lazy">
                    </div>
                    <div class="news-body">
                        <p class="news-tag tech">{{ __('welcome.news.tag_tech') }}</p>
                        <p class="news-title">{{ __('welcome.news.n2_title') }}</p>
                        <p class="news-desc">{{ __('welcome.news.n2_desc') }}</p>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-img-placeholder">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=700&q=80&auto=format&fit=crop" alt="Business meeting" loading="lazy">
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
                <h3>{{ __('welcome.brand.h3') }}</h3>
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
                <h4>{{ __('welcome.brand.benefits_h') }}</h4>
                <div class="brand-benefit">{{ __('welcome.brand.benefit1') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit2') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit3') }}</div>
                <div class="brand-benefit">{{ __('welcome.brand.benefit4') }}</div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="cta-banner">
        <div class="container">
            <h2>{{ __('welcome.cta.h2') }}</h2>
            <p>{{ __('welcome.cta.sub') }}</p>
            <a href="#" class="btn btn-primary btn-lg">{{ __('welcome.cta.btn') }}</a>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq">
        <div class="container">
            <div class="faq-inner">
                <p class="faq-title">{{ __('welcome.faq.title') }}</p>
                @foreach(range(1, 5) as $i)
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        {{ __("welcome.faq.q{$i}") }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="faq-a"><div class="faq-a-inner">{{ __("welcome.faq.a{$i}") }}</div></div>
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
