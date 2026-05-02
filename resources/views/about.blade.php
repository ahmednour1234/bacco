@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', __('about.nav.about') . ' — QIMTA')

@section('nav-cta')
    <a href="#" class="btn-demo">{{ __('about.nav.get_demo') }}</a>
@endsection

@section('mobile-cta')
    <a href="#" class="btn-demo">{{ __('about.nav.get_demo') }}</a>
@endsection

@section('styles')
<style>
    /* ── ABOUT PAGE OVERRIDES & VARIABLES ── */
    :root {
        --gray: #666;
    }
    .container { max-width: 1080px; padding: 0 32px; }

    /* ── HERO ── */
    .hero { background: var(--cream); padding: 48px 0 56px; }
    .hero-card { border-radius: 14px; overflow: hidden; display: flex; flex-direction: row; min-height: 340px; }
    .hero-text { flex: 0 0 52%; display: flex; align-items: center; padding: 48px 40px; }
    .hero-text-inner { width: 100%; }
    .hero-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 18px; display: block; }
    [dir="rtl"] .hero-label { letter-spacing: 0; }
    .hero h1 { font-size: 40px; font-weight: 800; line-height: 1.1; color: var(--dark); letter-spacing: -1.5px; margin-bottom: 18px; }
    [dir="rtl"] .hero h1 { letter-spacing: 0; }
    .hero-sub { font-size: 14px; color: var(--gray); line-height: 1.8; margin-bottom: 0; }
    .hero-img-wrap { flex: 1; min-width: 0; }
    .hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }

    /* ── MISSION ── */
    .mission { background: var(--white); padding: 80px 0; text-align: center; border-bottom: 1px solid var(--border); }
    .mission-label { font-size: 11px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: var(--green); margin-bottom: 20px; }
    [dir="rtl"] .mission-label { letter-spacing: 0; }
    .mission-text { font-size: 28px; font-weight: 700; color: var(--dark); line-height: 1.4; max-width: 600px; margin: 0 auto; }
    .mission-text .accent { color: var(--green); font-style: italic; }

    /* ── PROBLEM vs STANDARD ── */
    .pvs { background: var(--cream); padding: 64px 0; }
    .pvs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .pvs-card { border-radius: 14px; padding: 32px; }
    .pvs-card.problem-col { background: var(--white); border: 1px solid var(--border); }
    .pvs-card.standard-col { background: var(--white); border: 1px solid #86efac; }
    .pvs-header { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; }
    .pvs-icon { font-size: 18px; }
    .pvs-header-title { font-size: 16px; font-weight: 700; color: var(--dark); }
    .pvs-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 22px; padding-bottom: 22px; border-bottom: 1px solid rgba(0,0,0,0.06); }
    .pvs-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .pvs-item-bar { width: 3px; border-radius: 3px; align-self: stretch; flex-shrink: 0; min-height: 40px; }
    .problem-col .pvs-item-bar { background: #f87171; }
    .standard-col .pvs-item-bar { background: var(--green); }
    .pvs-item-title { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 5px; }
    .pvs-item-desc { font-size: 13px; color: var(--gray); line-height: 1.65; }

    /* ── STATS DARK BAR ── */
    .stats-bar { background: #0d1117; padding: 48px 0; border: 1px solid #3F3F46; }
    .stats-bar-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #5DFFA3; margin-bottom: 32px; }
    [dir="rtl"] .stats-bar-label { letter-spacing: 0; }
    .stats-bar-grid { display: flex; gap: 48px; align-items: flex-end; flex-wrap: wrap; }
    .stat-item .stat-val { font-family: 'Cairo', sans-serif; font-size: 38px; font-weight: 800; color: #5DFFA3; letter-spacing: -2px; line-height: 1; margin-bottom: 6px; }
    [dir="rtl"] .stat-item .stat-val { letter-spacing: 0; }
    .stat-item .stat-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #888; }
    [dir="rtl"] .stat-item .stat-label { letter-spacing: 0; }

    /* ── APPROACH ── */
    .approach-section { padding: 64px 0; background: #f8fafb; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
    .approach-header { border: 1.5px dashed #b0c4b1; border-radius: 12px; padding: 24px 28px; margin-bottom: 24px; }
    .approach-header h2 { font-size: 22px; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
    .approach-header p { font-size: 14px; color: var(--gray); }
    .approach-outer { border: 1.5px dashed #b0c4b1; border-radius: 12px; padding: 24px; }
    .approach-grid { display: grid; grid-template-columns: repeat(3, 1fr); }
    .approach-card { padding: 28px 24px; position: relative; }
    .approach-card + .approach-card { border-left: 1px solid var(--border); }
    [dir="rtl"] .approach-card + .approach-card { border-left: none; border-right: 1px solid var(--border); }
    .approach-icon-wrap { width: 42px; height: 42px; border-radius: 10px; background: var(--green-light); display: flex; align-items: center; justify-content: center; margin-bottom: 18px; color: var(--green); }
    .approach-card h3 { font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .approach-card p { font-size: 13.5px; color: var(--gray); line-height: 1.7; }

    /* ── GULF ── */
    .gulf { padding: 72px 0; background: var(--white); }
    .gulf-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
    .gulf h2 { font-size: 28px; font-weight: 800; color: var(--dark); margin-bottom: 16px; line-height: 1.2; letter-spacing: -0.5px; }
    [dir="rtl"] .gulf h2 { letter-spacing: 0; }
    .gulf p { font-size: 14px; color: var(--gray); line-height: 1.8; margin-bottom: 24px; }
    .gulf-tags { display: flex; gap: 10px; flex-wrap: wrap; }
    .gulf-tag { border: 1px solid var(--border); border-radius: 20px; padding: 6px 16px; font-size: 13px; font-weight: 600; color: #444; }
    .gulf-img { border-radius: 14px; overflow: hidden; }
    .gulf-img img { width: 100%; height: 300px; object-fit: cover; display: block; }

    /* ── CTA ── */
    .cta-wrap { background: var(--cream); padding: 0 0 48px; }
    .cta { background: var(--green); padding: 72px 32px; border-radius: 16px; margin: 48px auto; max-width: 1040px; text-align: center; }
    .cta h2 { font-size: 36px; font-weight: 800; color: var(--white); line-height: 1.2; letter-spacing: -1px; margin-bottom: 16px; white-space: pre-line; }
    [dir="rtl"] .cta h2 { letter-spacing: 0; }
    .cta p { font-size: 14px; color: rgba(255,255,255,0.75); max-width: 480px; margin: 0 auto 32px; line-height: 1.75; }
    .cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .btn-cta-primary { background: var(--white); color: var(--green); font-size: 14px; font-weight: 700; padding: 13px 26px; border-radius: 8px; transition: all .2s; }
    .btn-cta-primary:hover { background: #f0f0f0; }
    .btn-cta-outline { background: transparent; color: var(--white); border: 1.5px solid rgba(255,255,255,0.55); font-size: 14px; font-weight: 600; padding: 13px 26px; border-radius: 8px; transition: all .2s; }
    .btn-cta-outline:hover { border-color: var(--white); background: rgba(255,255,255,0.08); }

    /* ── RESPONSIVE (page-specific) ── */
    @media (max-width: 900px) {
        .gulf-inner, .pvs-grid { grid-template-columns: 1fr; }
        .hero-card { flex-direction: column; min-height: unset; }
        .hero-text { flex: none; padding: 32px 24px; }
        .hero-img-wrap { width: 100%; height: 220px; }
        .hero-img-wrap img { height: 220px; }
        .approach-grid { grid-template-columns: 1fr; }
        .approach-card + .approach-card { border-left: none; border-top: 1px solid var(--border); }
        [dir="rtl"] .approach-card + .approach-card { border-right: none; border-top: 1px solid var(--border); }
        .stats-bar-grid { gap: 28px; }
        .cta h2 { font-size: 26px; }
        .cta { margin: 24px 16px; padding: 48px 20px; }
    }
    @media (max-width: 768px) {
        .hero h1 { font-size: 26px; }
        .hero-text { padding: 24px 20px; }
        .mission-text { font-size: 22px; }
        .pvs-grid { gap: 14px; }
    }
</style>
@endsection

@section('content')

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-card">
                <div class="hero-text">
                    <div class="hero-text-inner">
                        <span class="hero-label">{{ __('about.hero.label') }}</span>
                        <h1>{{ __('about.hero.h1') }}</h1>
                        <p class="hero-sub">{{ __('about.hero.sub') }}</p>
                    </div>
                </div>
                <div class="hero-img-wrap">
                    <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=900&q=80&auto=format&fit=crop" alt="{{ __('about.hero.img_alt') }}" loading="eager">
                </div>
            </div>
        </div>
    </section>

    <!-- MISSION -->
    <section class="mission">
        <div class="container">
            <p class="mission-label">{{ __('about.mission.label') }}</p>
            <p class="mission-text">
                {{ __('about.mission.text') }} <span class="accent">{{ __('about.mission.free') }}</span> {{ __('about.mission.text2') }}
            </p>
        </div>
    </section>

    <!-- PROBLEM vs STANDARD -->
    <section class="pvs">
        <div class="container">
            <div class="pvs-grid">
                <div class="pvs-card problem-col">
                    <div class="pvs-header">
                        <span class="pvs-icon" style="color:#f87171;">&#9888;</span>
                        <span class="pvs-header-title">{{ __('about.problem.col1_title') }}</span>
                    </div>
                    <div class="pvs-item">
                        <div class="pvs-item-bar"></div>
                        <div>
                            <p class="pvs-item-title">{{ __('about.problem.p1_title') }}</p>
                            <p class="pvs-item-desc">{{ __('about.problem.p1_desc') }}</p>
                        </div>
                    </div>
                    <div class="pvs-item">
                        <div class="pvs-item-bar"></div>
                        <div>
                            <p class="pvs-item-title">{{ __('about.problem.p2_title') }}</p>
                            <p class="pvs-item-desc">{{ __('about.problem.p2_desc') }}</p>
                        </div>
                    </div>
                    <div class="pvs-item">
                        <div class="pvs-item-bar"></div>
                        <div>
                            <p class="pvs-item-title">{{ __('about.problem.p3_title') }}</p>
                            <p class="pvs-item-desc">{{ __('about.problem.p3_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="pvs-card standard-col">
                    <div class="pvs-header">
                        <span class="pvs-icon" style="color:var(--green);">&#10004;</span>
                        <span class="pvs-header-title" style="color:var(--green);">{{ __('about.problem.col2_title') }}</span>
                    </div>
                    <div class="pvs-item">
                        <div class="pvs-item-bar"></div>
                        <div>
                            <p class="pvs-item-title">{{ __('about.problem.s1_title') }}</p>
                            <p class="pvs-item-desc">{{ __('about.problem.s1_desc') }}</p>
                        </div>
                    </div>
                    <div class="pvs-item">
                        <div class="pvs-item-bar"></div>
                        <div>
                            <p class="pvs-item-title">{{ __('about.problem.s2_title') }}</p>
                            <p class="pvs-item-desc">{{ __('about.problem.s2_desc') }}</p>
                        </div>
                    </div>
                    <div class="pvs-item">
                        <div class="pvs-item-bar"></div>
                        <div>
                            <p class="pvs-item-title">{{ __('about.problem.s3_title') }}</p>
                            <p class="pvs-item-desc">{{ __('about.problem.s3_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS DARK BAR -->
    <section class="stats-bar">
        <div class="container">
            <p class="stats-bar-label">{{ __('about.stats.label') }}</p>
            <div class="stats-bar-grid">
                <div class="stat-item">
                    <div class="stat-val">418,326</div>
                    <div class="stat-label">{{ __('about.stats.products') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">~1B</div>
                    <div class="stat-label">{{ __('about.stats.specs') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">15</div>
                    <div class="stat-label">{{ __('about.stats.divisions') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">206</div>
                    <div class="stat-label">{{ __('about.stats.categories') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">RAG</div>
                    <div class="stat-label">{{ __('about.stats.engine') }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- APPROACH -->
    <section class="approach-section">
        <div class="container">
            <div class="approach-header">
                <h2>{{ __('about.approach.title') }}</h2>
                <p>{{ __('about.approach.sub') }}</p>
            </div>
            <div class="approach-outer">
                <div class="approach-grid">
                    <div class="approach-card">
                        <div class="approach-icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>
                        </div>
                        <h3>{{ __('about.approach.a1_title') }}</h3>
                        <p>{{ __('about.approach.a1_desc') }}</p>
                    </div>
                    <div class="approach-card">
                        <div class="approach-icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <h3>{{ __('about.approach.a2_title') }}</h3>
                        <p>{{ __('about.approach.a2_desc') }}</p>
                    </div>
                    <div class="approach-card">
                        <div class="approach-icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                        <h3>{{ __('about.approach.a3_title') }}</h3>
                        <p>{{ __('about.approach.a3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- GULF -->
    <section class="gulf">
        <div class="container">
            <div class="gulf-inner">
                <div>
                    <h2>{{ __('about.gulf.h2') }}</h2>
                    <p>{{ __('about.gulf.sub') }}</p>
                    <div class="gulf-tags">
                        <span class="gulf-tag">{{ __('about.gulf.sa') }}</span>
                        <span class="gulf-tag">{{ __('about.gulf.uae') }}</span>
                        <span class="gulf-tag">{{ __('about.gulf.qa') }}</span>
                    </div>
                </div>
                <div class="gulf-img">
                    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900&q=80&auto=format&fit=crop" alt="{{ __('about.gulf.img_alt') }}" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <div class="cta-wrap">
        <div class="container" style="padding: 0;">
            <div class="cta">
                <h2>{{ __('about.cta.h2') }}</h2>
                <p>{{ __('about.cta.sub') }}</p>
                <div class="cta-btns">
                    <a href="#" class="btn-cta-primary">{{ __('about.cta.btn_free') }}</a>
                    <a href="#" class="btn-cta-outline">{{ __('about.cta.btn_contact') }}</a>
                </div>
            </div>
        </div>
    </div>

@endsection
