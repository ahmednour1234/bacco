@extends('layouts.app')

@section('title', __('security.hero.h1'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<style>
/* ── SHARED ──────────────────────────────────────────────────────────────── */
.sec-page { font-family: 'Cairo', 'Inter', sans-serif; color: var(--dark); }

/* ── HERO ────────────────────────────────────────────────────────────────── */
.sec-hero {
    background: var(--cream);
    padding: 96px 0 80px;
}
.sec-hero-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 1fr 440px;
    gap: 64px;
    align-items: center;
}
.sec-hero-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0,106,59,0.1);
    color: var(--green);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    padding: 6px 12px;
    border-radius: 20px;
    margin-bottom: 24px;
}
.sec-hero-label svg { width:12px; height:12px; }
.sec-hero h1 {
    font-size: clamp(32px, 4.5vw, 52px);
    font-weight: 800;
    line-height: 1.1;
    margin: 0 0 20px;
    color: var(--dark);
}
.sec-hero-sub {
    font-size: 15px;
    color: var(--gray);
    line-height: 1.75;
    max-width: 460px;
    margin-bottom: 36px;
}
.sec-hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }
.sec-btn-primary {
    background: var(--green);
    color: #fff;
    border: none;
    padding: 14px 28px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    font-family: 'Cairo', 'Inter', sans-serif;
    transition: background .2s;
}
.sec-btn-primary:hover { background: #005530; }
.sec-btn-outline {
    background: transparent;
    color: var(--dark);
    border: 2px solid var(--dark);
    padding: 13px 28px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    font-family: 'Cairo', 'Inter', sans-serif;
    transition: background .2s, color .2s;
}
.sec-btn-outline:hover { background: var(--dark); color: #fff; }

/* Encryption card */
.enc-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 28px 28px 24px;
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
    position: relative;
}
.enc-card-id {
    position: absolute;
    top: 20px;
    right: 24px;
    font-size: 11px;
    color: #aaa;
    letter-spacing: .08em;
}
[dir=rtl] .enc-card-id { right: auto; left: 24px; }
.enc-card-title {
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 22px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--green);
    display: inline-block;
}
.enc-rows { display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px; }
.enc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 14px;
    border-bottom: 1px solid #f0f0f0;
}
.enc-row:last-child { border-bottom: none; padding-bottom: 0; }
.enc-row-label { font-size: 13px; color: var(--gray); }
.enc-row-value { font-size: 13px; font-weight: 700; color: var(--dark); }
.enc-quote {
    background: #f9f9f7;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 14px 16px;
    font-size: 12.5px;
    color: var(--gray);
    line-height: 1.6;
    font-style: italic;
    margin-top: 4px;
}

/* ── PILLARS ─────────────────────────────────────────────────────────────── */
.sec-pillars {
    background: var(--cream);
    padding: 88px 0;
}
.sec-pillars-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}
.sec-section-header { margin-bottom: 48px; }
.sec-section-header h2 {
    font-size: clamp(26px, 3vw, 38px);
    font-weight: 800;
    margin: 0 0 12px;
}
.sec-section-header p {
    font-size: 15px;
    color: var(--gray);
    max-width: 600px;
    line-height: 1.7;
}
.pillars-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.pillar-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 28px 24px;
    transition: box-shadow .2s;
}
.pillar-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.pillar-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    color: var(--green);
}
.pillar-icon svg { width: 26px; height: 26px; }
.pillar-card h3 { font-size: 16px; font-weight: 700; margin: 0 0 10px; }
.pillar-card p { font-size: 13.5px; color: var(--gray); line-height: 1.7; margin: 0; }

/* ── COMPLIANCE ──────────────────────────────────────────────────────────── */
.sec-compliance {
    background: #edecea;
    padding: 80px 0;
}
.sec-compliance-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 64px;
    align-items: start;
}
.compliance-left h2 {
    font-size: clamp(24px, 2.8vw, 34px);
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 14px;
}
.compliance-left p {
    font-size: 14px;
    color: var(--gray);
    line-height: 1.7;
}
.compliance-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.compliance-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 28px 22px;
    text-align: center;
}
.compliance-card-icon {
    width: 52px;
    height: 52px;
    background: rgba(0,106,59,.08);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: var(--green);
}
.compliance-card-icon svg { width: 24px; height: 24px; }
.compliance-card h3 { font-size: 15px; font-weight: 700; margin: 0 0 8px; }
.compliance-card p { font-size: 13px; color: var(--gray); line-height: 1.65; margin: 0; }

/* ── LIFECYCLE ───────────────────────────────────────────────────────────── */
.sec-lifecycle {
    background: var(--cream);
    padding: 88px 0;
}
.sec-lifecycle-inner {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 24px;
    text-align: center;
}
.sec-lifecycle-inner .sec-section-header { text-align: center; }
.sec-lifecycle-inner .sec-section-header p { margin: 0 auto; }
.lifecycle-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 0;
    text-align: left;
}
[dir=rtl] .lifecycle-grid { text-align: right; }
.lifecycle-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.lifecycle-arrow {
    color: #ccc;
    flex-shrink: 0;
}
.lifecycle-arrow svg { width: 22px; height: 22px; }
.step-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 22px 20px;
    flex: 1;
}
.step-card.complete-card {
    background: var(--green);
    border-color: var(--green);
    color: #fff;
    text-align: center;
}
.step-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--green);
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.step-card.complete-card .step-label { color: rgba(255,255,255,.7); }
.step-icon {
    margin-bottom: 10px;
    color: var(--green);
}
.step-card.complete-card .step-icon { color: #fff; }
.step-icon svg { width: 26px; height: 26px; }
.step-title {
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--dark);
}
.step-card.complete-card .step-title { color: #fff; }
.step-desc {
    font-size: 12px;
    color: var(--gray);
    margin: 0;
}
.step-card.complete-card .step-desc { color: rgba(255,255,255,.75); }

/* lifecycle layout rows */
.lc-top-row, .lc-bottom-row {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 0;
}
.lc-connector {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #ccc;
    padding: 0 8px;
}
.lc-connector svg { width: 20px; height: 20px; }
.lc-middle {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 16px 0;
    position: relative;
}
.lc-blob {
    width: 52px;
    height: 28px;
    background: #c8c5be;
    border-radius: 50px;
    opacity: .5;
}
.lc-down-arrow {
    color: #ccc;
    position: absolute;
    bottom: -4px;
}
.lc-down-arrow svg { width: 18px; height: 18px; }
.lc-bottom-row { justify-content: flex-end; gap: 8px; }
.lc-bottom-row .lc-connector { transform: rotate(90deg); }
/* override: bottom row aligns left side */
.lc-steps-wrapper {
    margin-top: 48px;
}
.lc-row1 {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 0;
    margin-bottom: 0;
}
.lc-mid {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    padding: 16px 0;
    padding-right: calc(50% - 26px);
    gap: 6px;
}
[dir=rtl] .lc-mid { padding-right: 0; padding-left: calc(50% - 26px); }
.lc-row2 {
    display: grid;
    grid-template-columns: repeat(5, auto);
    justify-content: end;
    align-items: center;
    gap: 0;
}

/* ── FAQ ─────────────────────────────────────────────────────────────────── */
.sec-faq {
    background: var(--cream);
    padding: 88px 0;
    border-top: 1px solid var(--border);
}
.sec-faq-inner {
    max-width: 680px;
    margin: 0 auto;
    padding: 0 24px;
}
.sec-faq-inner .sec-section-header { text-align: center; }
.sec-faq-inner .sec-section-header p { margin: 0 auto; }
.faq-list { display: flex; flex-direction: column; gap: 0; }
.faq-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-bottom: 10px;
    background: #fff;
    overflow: hidden;
}
.faq-question {
    width: 100%;
    background: none;
    border: none;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 20px;
    font-size: 14.5px;
    font-weight: 600;
    color: var(--dark);
    cursor: pointer;
    font-family: 'Cairo', 'Inter', sans-serif;
    gap: 12px;
}
[dir=rtl] .faq-question { text-align: right; }
.faq-question svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    color: var(--gray);
    transition: transform .25s;
}
.faq-item.open .faq-question svg { transform: rotate(180deg); }
.faq-answer {
    display: none;
    padding: 0 20px 18px;
    font-size: 13.5px;
    color: var(--gray);
    line-height: 1.75;
}
.faq-item.open .faq-answer { display: block; }

/* ── CTA ─────────────────────────────────────────────────────────────────── */
.sec-cta {
    background: var(--green);
    padding: 48px 24px;
}
.sec-cta-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 48px;
    flex-wrap: wrap;
}
.sec-cta-text p:first-child {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 10px;
}
.sec-cta-text p:last-child {
    font-size: 13.5px;
    color: rgba(255,255,255,.85);
    line-height: 1.7;
    margin: 0;
    max-width: 480px;
}
.sec-cta-btn {
    background: #fff;
    color: var(--green);
    border: 2px solid #fff;
    padding: 14px 32px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    font-family: 'Cairo', 'Inter', sans-serif;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .2s, color .2s;
}
.sec-cta-btn:hover { background: #e8f5ef; color: var(--green); }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 960px) {
    .sec-hero-inner { grid-template-columns: 1fr; }
    .enc-card { max-width: 480px; }
    .pillars-grid { grid-template-columns: 1fr 1fr; }
    .sec-compliance-inner { grid-template-columns: 1fr; }
    .compliance-cards { grid-template-columns: 1fr; }
    .lc-row1 { grid-template-columns: 1fr; }
    .lc-connector { display: none; }
    .lc-row2 { display: flex; flex-direction: column; align-items: stretch; }
    .lc-mid { align-items: center; padding: 8px 0; }
}
@media (max-width: 640px) {
    .pillars-grid { grid-template-columns: 1fr; }
    .compliance-cards { grid-template-columns: 1fr; }
    .sec-hero-btns { flex-direction: column; }
    .sec-cta-inner { flex-direction: column; text-align: center; }
}
</style>

<div class="sec-page">

    {{-- ── HERO ──────────────────────────────────────────────────────────── --}}
    <section class="sec-hero">
        <div class="sec-hero-inner">
            <div>
                <div class="sec-hero-label">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2z"/></svg>
                    {{ __('security.hero.label') }}
                </div>
                <h1>{{ __('security.hero.h1') }}</h1>
                <p class="sec-hero-sub">{{ __('security.hero.sub') }}</p>
                <div class="sec-hero-btns">
                    <a href="{{ route('contact') }}" class="sec-btn-primary">{{ __('security.hero.btn_talk') }}</a>
                    <a href="#" class="sec-btn-outline">{{ __('security.hero.btn_paper') }}</a>
                </div>
            </div>

            <div class="enc-card">
                <span class="enc-card-id">{{ __('security.hero.card_id') }}</span>
                <div class="enc-card-title">{{ __('security.hero.card_title') }}</div>
                <div class="enc-rows">
                    <div class="enc-row">
                        <span class="enc-row-label">{{ __('security.hero.row1_label') }}</span>
                        <span class="enc-row-value">{{ __('security.hero.row1_value') }}</span>
                    </div>
                    <div class="enc-row">
                        <span class="enc-row-label">{{ __('security.hero.row2_label') }}</span>
                        <span class="enc-row-value">{{ __('security.hero.row2_value') }}</span>
                    </div>
                    <div class="enc-row">
                        <span class="enc-row-label">{{ __('security.hero.row3_label') }}</span>
                        <span class="enc-row-value">{{ __('security.hero.row3_value') }}</span>
                    </div>
                </div>
                <div class="enc-quote">{{ __('security.hero.card_quote') }}</div>
            </div>
        </div>
    </section>

    {{-- ── PILLARS ───────────────────────────────────────────────────────── --}}
    <section class="sec-pillars">
        <div class="sec-pillars-inner">
            <div class="sec-section-header">
                <h2>{{ __('security.pillars.label') }}</h2>
                <p>{{ __('security.pillars.sub') }}</p>
            </div>
            <div class="pillars-grid">
                {{-- Project isolation --}}
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/>
                            <line x1="12" y1="7" x2="5" y2="17"/><line x1="12" y1="7" x2="19" y2="17"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.pillars.p1_title') }}</h3>
                    <p>{{ __('security.pillars.p1_desc') }}</p>
                </div>
                {{-- Account protection --}}
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2z"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.pillars.p2_title') }}</h3>
                    <p>{{ __('security.pillars.p2_desc') }}</p>
                </div>
                {{-- Secure BOQ --}}
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.pillars.p3_title') }}</h3>
                    <p>{{ __('security.pillars.p3_desc') }}</p>
                </div>
                {{-- Access control --}}
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.pillars.p4_title') }}</h3>
                    <p>{{ __('security.pillars.p4_desc') }}</p>
                </div>
                {{-- Encryption --}}
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.pillars.p5_title') }}</h3>
                    <p>{{ __('security.pillars.p5_desc') }}</p>
                </div>
                {{-- Monitoring --}}
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.pillars.p6_title') }}</h3>
                    <p>{{ __('security.pillars.p6_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── COMPLIANCE ────────────────────────────────────────────────────── --}}
    <section class="sec-compliance">
        <div class="sec-compliance-inner">
            <div class="compliance-left">
                <h2>{{ __('security.compliance.title') }}</h2>
                <p>{{ __('security.compliance.sub') }}</p>
            </div>
            <div class="compliance-cards">
                <div class="compliance-card">
                    <div class="compliance-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.compliance.c1_title') }}</h3>
                    <p>{{ __('security.compliance.c1_desc') }}</p>
                </div>
                <div class="compliance-card">
                    <div class="compliance-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.compliance.c2_title') }}</h3>
                    <p>{{ __('security.compliance.c2_desc') }}</p>
                </div>
                <div class="compliance-card">
                    <div class="compliance-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <h3>{{ __('security.compliance.c3_title') }}</h3>
                    <p>{{ __('security.compliance.c3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── LIFECYCLE ─────────────────────────────────────────────────────── --}}
    <section class="sec-lifecycle">
        <div class="sec-lifecycle-inner">
            <div class="sec-section-header">
                <h2>{{ __('security.lifecycle.title') }}</h2>
                <p>{{ __('security.lifecycle.sub') }}</p>
            </div>

            <div class="lc-steps-wrapper">
                {{-- Row 1: Steps 1–3 --}}
                <div class="lc-row1">
                    {{-- Step 01 --}}
                    <div class="step-card">
                        <div class="step-label">{{ __('security.lifecycle.s1_label') }}</div>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="12" y2="12"/><line x1="15" y1="15" x2="12" y2="12"/>
                            </svg>
                        </div>
                        <div class="step-title">{{ __('security.lifecycle.s1_title') }}</div>
                        <div class="step-desc">{{ __('security.lifecycle.s1_desc') }}</div>
                    </div>
                    <div class="lc-connector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 18 15 12 9 6"/></svg></div>
                    {{-- Step 02 --}}
                    <div class="step-card">
                        <div class="step-label">{{ __('security.lifecycle.s2_label') }}</div>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                                <path d="M9 8l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="step-title">{{ __('security.lifecycle.s2_title') }}</div>
                        <div class="step-desc">{{ __('security.lifecycle.s2_desc') }}</div>
                    </div>
                    <div class="lc-connector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 18 15 12 9 6"/></svg></div>
                    {{-- Step 03 --}}
                    <div class="step-card">
                        <div class="step-label">{{ __('security.lifecycle.s3_label') }}</div>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            </svg>
                        </div>
                        <div class="step-title">{{ __('security.lifecycle.s3_title') }}</div>
                        <div class="step-desc">{{ __('security.lifecycle.s3_desc') }}</div>
                    </div>
                </div>

                {{-- Middle connector + blob --}}
                <div class="lc-mid">
                    <div class="lc-blob"></div>
                    <div style="color:#ccc; margin-top:4px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                </div>

                {{-- Row 2: Step 04 + Complete --}}
                <div class="lc-row2">
                    {{-- Step 04 --}}
                    <div class="step-card" style="min-width:180px; max-width:220px; text-align:{{ $isAr ? 'right' : 'left' }}">
                        <div class="step-label">{{ __('security.lifecycle.s4_label') }}</div>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/>
                            </svg>
                        </div>
                        <div class="step-title">{{ __('security.lifecycle.s4_title') }}</div>
                        <div class="step-desc">{{ __('security.lifecycle.s4_desc') }}</div>
                    </div>
                    <div class="lc-connector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 18 15 12 9 6"/></svg></div>
                    {{-- Complete --}}
                    <div class="step-card complete-card" style="min-width:160px; max-width:200px;">
                        <div class="step-label">{{ __('security.lifecycle.sc_label') }}</div>
                        <div class="step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.1L1 10"/>
                            </svg>
                        </div>
                        <div class="step-title">{{ __('security.lifecycle.sc_title') }}</div>
                        <div class="step-desc">{{ __('security.lifecycle.sc_desc') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── FAQ ─────────────────────────────────────────────────────────────--}}
    <section class="sec-faq">
        <div class="sec-faq-inner">
            <div class="sec-section-header">
                <h2>{{ __('security.faq.title') }}</h2>
                <p>{{ __('security.faq.sub') }}</p>
            </div>
            <div class="faq-list">
                @foreach(['1','2','3','4','5'] as $i)
                <div class="faq-item{{ $i === '1' ? ' open' : '' }}">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        {{ __("security.faq.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="faq-answer">{{ __("security.faq.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA ───────────────────────────────────────────────────────────── --}}
    <section class="sec-cta">
        <div class="sec-cta-inner">
            <div class="sec-cta-text">
                <p>{{ __('security.cta.label') }}</p>
                <p>{{ __('security.cta.sub') }}</p>
            </div>
            <a href="{{ route('contact') }}" class="sec-cta-btn">{{ __('security.cta.btn') }}</a>
        </div>
    </section>

</div>

<script>
function toggleFaq(btn) {
    var item = btn.closest('.faq-item');
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(function(el) {
        el.classList.remove('open');
    });
    if (!isOpen) item.classList.add('open');
}
</script>
@endsection
