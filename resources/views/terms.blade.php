@extends('layouts.app')

@section('title', __('terms.hero.title'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<style>
/* ── PAGE SHELL ──────────────────────────────────────────────────────────── */
.terms-page {
    font-family: 'Cairo','Inter',sans-serif;
    color: var(--dark);
    background: #fff;
    min-height: 100vh;
}

/* ── MAIN CONTENT ────────────────────────────────────────────────────────── */
.terms-main {
    padding: 48px 56px 80px;
    max-width: 820px;
}
@media (max-width: 900px) { .terms-main { padding: 40px 28px 60px; } }

/* Hero */
.terms-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 10px;
}
.terms-h1 {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 800;
    color: var(--dark);
    line-height: 1.1;
}
.terms-ar {
    font-size: clamp(18px, 2.5vw, 28px);
    font-weight: 800;
    color: var(--green);
    white-space: nowrap;
}
.terms-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 28px;
}
.terms-badge {
    background: rgba(0,106,59,.1);
    color: var(--green);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    padding: 4px 10px;
    border-radius: 4px;
}
.terms-updated { font-size: 13px; color: var(--gray); }

/* Stat cards */
.terms-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 40px;
}
.stat-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px 18px;
}
.stat-card:last-child { border-color: #e85252; }
.stat-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 6px;
}
.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark);
}

/* Section divider */
.terms-section {
    margin-bottom: 52px;
    scroll-margin-top: 88px;
}
.terms-section h2 {
    font-size: clamp(20px, 2.5vw, 26px);
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1.5px dashed var(--border);
}

/* Paragraphs */
.terms-section p {
    font-size: 14px;
    color: var(--gray);
    line-height: 1.8;
    margin-bottom: 14px;
}
.terms-section p:last-child { margin-bottom: 0; }

/* Bullet list box */
.terms-list-box {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.terms-list-box li {
    font-size: 14px;
    color: var(--gray);
    line-height: 1.7;
    list-style: none;
    padding-left: 0;
}
[dir=rtl] .terms-list-box li { padding-right: 0; }

/* Brand cards */
.terms-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.terms-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px 18px;
}
.terms-card h4 { font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--dark); }
.terms-card p  { font-size: 13px; color: var(--gray); line-height: 1.65; margin: 0; }

/* Pricing table */
.terms-table {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 8px;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    font-size: 13.5px;
}
.terms-table thead tr { background: #f7f7f5; }
.terms-table th {
    text-align: left;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 600;
    color: var(--gray);
    letter-spacing: .04em;
    border-bottom: 1px solid var(--border);
}
[dir=rtl] .terms-table th { text-align: right; }
.terms-table td {
    padding: 14px 16px;
    color: var(--dark);
    border-bottom: 1px solid #f0f0f0;
}
.terms-table tr:last-child td { border-bottom: none; }
.terms-table td:first-child { font-weight: 700; }

/* Ordering note box */
.terms-note-box {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px 24px;
    font-size: 14px;
    color: var(--gray);
    line-height: 1.8;
}
.terms-note-box a { color: var(--green); font-weight: 600; text-decoration: underline; }

/* Account section: text + image */
.terms-account-grid {
    display: grid;
    grid-template-columns: 1fr 200px;
    gap: 20px;
    align-items: start;
}
.terms-warning {
    background: #fff5f5;
    border: 1px solid #f5c2c2;
    border-radius: 6px;
    padding: 12px 16px;
    margin-top: 14px;
}
.terms-warning-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    color: #c0392b;
    margin-bottom: 4px;
}
.terms-warning p {
    font-size: 13px;
    color: #c0392b;
    margin: 0;
    line-height: 1.6;
}
.terms-circuit-img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #1a1a1a;
}

/* Acceptable use grid */
.terms-use-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.terms-use-item {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 12px 16px;
    font-size: 13.5px;
    color: var(--dark);
}
.terms-use-ok svg  { color: var(--green); }
.terms-use-no svg  { color: #e05252; }
.terms-use-item svg { width: 16px; height: 16px; flex-shrink: 0; }

/* Limitations blockquote */
.terms-quote {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 22px 24px;
    font-size: 14px;
    color: var(--gray);
    line-height: 1.85;
    font-style: italic;
}

/* Contact 2-col */
.terms-contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.terms-contact-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px 18px;
}
.terms-contact-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 8px;
}
.terms-contact-card a { color: var(--dark); font-weight: 700; font-size: 14px; text-decoration: none; }
.terms-contact-card a:hover { color: var(--green); }
.terms-contact-card p { font-size: 13.5px; color: var(--dark); font-weight: 600; line-height: 1.6; margin: 0; }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 860px) {
    .terms-account-grid { grid-template-columns: 1fr; }
    .terms-circuit-img { max-width: 200px; }
}
@media (max-width: 600px) {
    .terms-stats { grid-template-columns: 1fr; }
    .terms-2col, .terms-use-grid, .terms-contact-grid { grid-template-columns: 1fr; }
}
</style>

<div class="terms-page">

    {{-- ── MAIN ─────────────────────────────────────────────────────────── --}}
    <main class="terms-main">

        {{-- Title row --}}
        <div class="terms-title-row">
            <h1 class="terms-h1">{{ __('terms.hero.title') }}</h1>
            <span class="terms-ar">{{ __('terms.hero.title_ar') }}</span>
        </div>
        <div class="terms-meta">
            <span class="terms-badge">{{ __('terms.hero.badge') }}</span>
            <span class="terms-updated">{{ __('terms.hero.updated') }}</span>
        </div>
        <hr style="border:none;border-top:1px solid var(--border);margin-bottom:24px;">

        {{-- Stat cards --}}
        <div class="terms-stats">
            <div class="stat-card">
                <div class="stat-label">{{ __('terms.stats.status_label') }}</div>
                <div class="stat-value">{{ __('terms.stats.status_value') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ __('terms.stats.jurisdiction_label') }}</div>
                <div class="stat-value">{{ __('terms.stats.jurisdiction_value') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ __('terms.stats.compliance_label') }}</div>
                <div class="stat-value">{{ __('terms.stats.compliance_value') }}</div>
            </div>
        </div>

        {{-- ── S1: Using Qimta ── --}}
        <section class="terms-section" id="s1">
            <h2>{{ __('terms.s1.title') }}</h2>
            <p>{{ __('terms.s1.p1') }}</p>
            <p>{{ __('terms.s1.p2') }}</p>
        </section>

        {{-- ── S2: Buyer responsibilities ── --}}
        <section class="terms-section" id="s2">
            <h2>{{ __('terms.s2.title') }}</h2>
            <ul class="terms-list-box">
                <li>{{ __('terms.s2.b1') }}</li>
                <li>{{ __('terms.s2.b2') }}</li>
                <li>{{ __('terms.s2.b3') }}</li>
            </ul>
        </section>

        {{-- ── S3: Brand responsibilities ── --}}
        <section class="terms-section" id="s3">
            <h2>{{ __('terms.s3.title') }}</h2>
            <div class="terms-2col">
                <div class="terms-card">
                    <h4>{{ __('terms.s3.c1_title') }}</h4>
                    <p>{{ __('terms.s3.c1_desc') }}</p>
                </div>
                <div class="terms-card">
                    <h4>{{ __('terms.s3.c2_title') }}</h4>
                    <p>{{ __('terms.s3.c2_desc') }}</p>
                </div>
            </div>
        </section>

        {{-- ── S4: Pricing information ── --}}
        <section class="terms-section" id="s4">
            <h2>{{ __('terms.s4.title') }}</h2>
            <table class="terms-table">
                <thead>
                    <tr>
                        <th>{{ __('terms.s4.col1') }}</th>
                        <th>{{ __('terms.s4.col2') }}</th>
                        <th>{{ __('terms.s4.col3') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('terms.s4.r1_tier') }}</td>
                        <td>{{ __('terms.s4.r1_fee') }}</td>
                        <td>{{ __('terms.s4.r1_maint') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('terms.s4.r2_tier') }}</td>
                        <td>{{ __('terms.s4.r2_fee') }}</td>
                        <td>{{ __('terms.s4.r2_maint') }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        {{-- ── S5: Ordering through Qimta ── --}}
        <section class="terms-section" id="s5">
            <h2>{{ __('terms.s5.title') }}</h2>
            <div class="terms-note-box">
                {{ __('terms.s5.p1') }}
                <a href="{{ route('contact') }}">{{ __('terms.s5.link') }}</a>
                {{ __('terms.s5.p2') }}
            </div>
        </section>

        {{-- ── S6: Account access ── --}}
        <section class="terms-section" id="s6">
            <h2>{{ __('terms.s6.title') }}</h2>
            <div class="terms-account-grid">
                <div>
                    <p>{{ __('terms.s6.p1') }}</p>
                    <div class="terms-warning">
                        <div class="terms-warning-label">{{ __('terms.s6.warning_label') }}</div>
                        <p>{{ __('terms.s6.warning_text') }}</p>
                    </div>
                </div>
                {{-- Circuit board tech image --}}
                <img
                    src="{{ asset('images/circuit-board.svg') }}"
                    alt="Security Technology"
                    class="terms-circuit-img"
                    loading="lazy"
                >
            </div>
        </section>

        {{-- ── S7: Acceptable use ── --}}
        <section class="terms-section" id="s7">
            <h2>{{ __('terms.s7.title') }}</h2>
            <div class="terms-use-grid">
                <div class="terms-use-item terms-use-ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
                    {{ __('terms.s7.ok1') }}
                </div>
                <div class="terms-use-item terms-use-ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
                    {{ __('terms.s7.ok2') }}
                </div>
                <div class="terms-use-item terms-use-no">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ __('terms.s7.no1') }}
                </div>
                <div class="terms-use-item terms-use-no">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ __('terms.s7.no2') }}
                </div>
            </div>
        </section>

        {{-- ── S8: Limitations ── --}}
        <section class="terms-section" id="s8">
            <h2>{{ __('terms.s8.title') }}</h2>
            <div class="terms-quote">{{ __('terms.s8.quote') }}</div>
        </section>

        {{-- ── S9: Changes to service ── --}}
        <section class="terms-section" id="s9">
            <h2>{{ __('terms.s9.title') }}</h2>
            <div class="terms-note-box">{{ __('terms.s9.p1') }}</div>
        </section>

        {{-- ── S10: Contact ── --}}
        <section class="terms-section" id="s10">
            <h2>{{ __('terms.s10.title') }}</h2>
            <div class="terms-contact-grid">
                <div class="terms-contact-card">
                    <div class="terms-contact-label">{{ __('terms.s10.legal_label') }}</div>
                    <a href="mailto:{{ __('terms.s10.legal_email') }}">{{ __('terms.s10.legal_email') }}</a>
                </div>
                <div class="terms-contact-card">
                    <div class="terms-contact-label">{{ __('terms.s10.office_label') }}</div>
                    <p>{{ __('terms.s10.office_line1') }}<br>{{ __('terms.s10.office_line2') }}</p>
                </div>
            </div>
        </section>

    </main>
</div>

@endsection
