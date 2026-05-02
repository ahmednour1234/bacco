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
    display: grid;
    grid-template-columns: 220px 1fr;
    min-height: 100vh;
    align-items: start;
}

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.terms-sidebar {
    position: sticky;
    top: 68px;
    height: calc(100vh - 68px);
    overflow-y: auto;
    border-right: 1px solid var(--border);
    padding: 28px 0 40px;
    background: #fff;
    flex-shrink: 0;
}
[dir=rtl] .terms-sidebar {
    border-right: none;
    border-left: 1px solid var(--border);
}
.terms-sidebar::-webkit-scrollbar { width: 4px; }
.terms-sidebar::-webkit-scrollbar-track { background: transparent; }
.terms-sidebar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

.sb-heading {
    font-size: 13px;
    font-weight: 700;
    color: var(--dark);
    padding: 0 20px 2px;
}
.sb-version {
    font-size: 11px;
    color: #aaa;
    padding: 0 20px 18px;
}
.sb-nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    font-size: 13px;
    color: var(--gray);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: color .2s, border-color .2s, background .2s;
    line-height: 1.4;
}
[dir=rtl] .sb-nav-link {
    border-left: none;
    border-right: 3px solid transparent;
}
.sb-nav-link:hover { color: var(--dark); background: #f7f7f5; }
.sb-nav-link.active {
    color: var(--green);
    border-left-color: var(--green);
    background: rgba(0,106,59,.05);
    font-weight: 600;
}
[dir=rtl] .sb-nav-link.active {
    border-left-color: transparent;
    border-right-color: var(--green);
}
.sb-nav-link svg { width: 14px; height: 14px; flex-shrink: 0; }

.sb-divider { height: 1px; background: var(--border); margin: 14px 20px; }

.sb-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #aaa;
    padding: 0 20px 8px;
}
.sb-section-link {
    display: block;
    padding: 6px 20px;
    font-size: 12.5px;
    color: var(--gray);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: color .2s, border-color .2s;
}
[dir=rtl] .sb-section-link {
    border-left: none;
    border-right: 3px solid transparent;
}
.sb-section-link:hover { color: var(--dark); }
.sb-section-link.active {
    color: var(--green);
    border-left-color: var(--green);
    font-weight: 600;
}
[dir=rtl] .sb-section-link.active {
    border-left-color: transparent;
    border-right-color: var(--green);
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
    .terms-page { grid-template-columns: 1fr; }
    .terms-sidebar { position: static; height: auto; border: none; border-bottom: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 0; padding: 12px 0; }
    .sb-section-link, .sb-nav-link { padding: 6px 12px; border: none; }
    .terms-account-grid { grid-template-columns: 1fr; }
    .terms-circuit-img { max-width: 200px; }
}
@media (max-width: 600px) {
    .terms-stats { grid-template-columns: 1fr; }
    .terms-2col, .terms-use-grid, .terms-contact-grid { grid-template-columns: 1fr; }
}
</style>

<div class="terms-page">

    {{-- ── SIDEBAR ──────────────────────────────────────────────────────── --}}
    <aside class="terms-sidebar">
        <div class="sb-heading">{{ __('terms.sidebar.heading') }}</div>
        <div class="sb-version">{{ __('terms.sidebar.version') }}</div>

        <a href="{{ route('privacy') }}" class="sb-nav-link {{ Route::is('privacy') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            {{ __('terms.sidebar.privacy') }}
        </a>
        <a href="{{ route('terms') }}" class="sb-nav-link {{ Route::is('terms') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            {{ __('terms.sidebar.terms') }}
        </a>
        <a href="{{ route('cookie') }}" class="sb-nav-link {{ Route::is('cookie') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ __('terms.sidebar.cookie') }}
        </a>
        <a href="#" class="sb-nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            {{ __('terms.sidebar.compliance') }}
        </a>
        <a href="{{ route('security') }}" class="sb-nav-link {{ Route::is('security') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            {{ __('terms.sidebar.security') }}
        </a>

        <div class="sb-divider"></div>
        <div class="sb-label">{{ __('terms.sidebar.sections') }}</div>

        @foreach(range(1,10) as $i)
        <a href="#s{{ $i }}" class="sb-section-link" data-sec="s{{ $i }}">
            {{ __("terms.sidebar.s{$i}") }}
        </a>
        @endforeach
    </aside>

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

<script>
// Highlight sidebar section links on scroll
(function () {
    var links = document.querySelectorAll('.sb-section-link[data-sec]');
    var sections = [];
    links.forEach(function (l) {
        var el = document.getElementById(l.dataset.sec);
        if (el) sections.push({ el: el, link: l });
    });

    function onScroll() {
        var scrollY = window.pageYOffset + 100;
        var active = null;
        sections.forEach(function (s) {
            if (s.el.offsetTop <= scrollY) active = s;
        });
        links.forEach(function (l) { l.classList.remove('active'); });
        if (active) active.link.classList.add('active');
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>
@endsection
