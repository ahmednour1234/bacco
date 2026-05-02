@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', __('privacy.title') . ' — QIMTA')

@section('styles')
<style>
    :root { --gray:#666; }
    .container { max-width: 1080px; padding: 0 32px; }

    /* ── PAGE WRAPPER ── */
    .pp-page { background: var(--white); min-height: 80vh; }

    /* ── HEADER ── */
    .pp-header { padding: 52px 0 36px; border-bottom: 1px solid var(--border); }
    .pp-header-inner { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
    .pp-title { font-size: 40px; font-weight: 800; color: var(--dark); letter-spacing: -1.5px; line-height: 1; margin-bottom: 10px; }
    [dir="rtl"] .pp-title { letter-spacing: 0; }
    .pp-title-alt { font-size: 18px; font-weight: 600; color: #aaa; letter-spacing: -0.3px; }
    [dir="rtl"] .pp-title-alt { letter-spacing: 0; }
    .pp-updated { font-size: 11px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; }
    [dir="rtl"] .pp-updated { letter-spacing: 0; }

    /* ── BODY LAYOUT ── */
    .pp-body { display: grid; grid-template-columns: 200px 1fr; gap: 0; padding: 0 0 80px; }
    [dir="rtl"] .pp-body { direction: rtl; }

    /* ── SIDEBAR ── */
    .pp-sidebar { padding: 40px 24px 40px 0; }
    [dir="rtl"] .pp-sidebar { padding: 40px 0 40px 24px; }
    .pp-sidebar-inner { position: sticky; top: 88px; }
    .pp-sidebar-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #bbb; margin-bottom: 14px; }
    [dir="rtl"] .pp-sidebar-label { letter-spacing: 0; }
    .pp-sidebar nav a { display: block; font-size: 12px; color: #888; padding: 5px 0; text-decoration: none; line-height: 1.4; transition: color .15s; border-left: 2px solid transparent; padding-left: 10px; }
    [dir="rtl"] .pp-sidebar nav a { border-left: none; border-right: 2px solid transparent; padding-left: 0; padding-right: 10px; }
    .pp-sidebar nav a:hover,
    .pp-sidebar nav a.is-active { color: var(--green); border-left-color: var(--green); font-weight: 600; }
    [dir="rtl"] .pp-sidebar nav a:hover,
    [dir="rtl"] .pp-sidebar nav a.is-active { border-left-color: transparent; border-right-color: var(--green); }

    /* ── CONTENT ── */
    .pp-content { padding: 40px 0 0 40px; border-left: 1px solid var(--border); }
    [dir="rtl"] .pp-content { padding: 40px 40px 0 0; border-left: none; border-right: 1px solid var(--border); }

    /* Section spacing */
    .pp-section { margin-bottom: 56px; scroll-margin-top: 100px; }
    .pp-section:last-child { margin-bottom: 0; }

    /* Section heading */
    .pp-h2 { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 800; color: var(--dark); letter-spacing: -0.6px; margin-bottom: 16px; line-height: 1.2; }
    [dir="rtl"] .pp-h2 { letter-spacing: 0; }
    .pp-icon { font-size: 18px; color: var(--green); flex-shrink: 0; line-height: 1; }

    /* Body text */
    .pp-p { font-size: 13px; color: var(--gray); line-height: 1.8; margin-bottom: 14px; }
    .pp-p:last-child { margin-bottom: 0; }

    /* Blockquote box (BOQ section) */
    .pp-box { border: 1px solid var(--border); border-radius: 10px; padding: 22px 26px; margin: 16px 0; background: #fafafa; }
    .pp-box p { font-size: 13px; color: var(--gray); line-height: 1.8; margin-bottom: 14px; }
    .pp-box ul { list-style: none; padding: 0; margin: 14px 0; }
    .pp-box ul li { font-size: 13px; color: var(--gray); padding: 4px 0 4px 18px; position: relative; line-height: 1.65; }
    [dir="rtl"] .pp-box ul li { padding-left: 0; padding-right: 18px; }
    .pp-box ul li::before { content: '—'; position: absolute; left: 0; color: #ccc; font-size: 11px; top: 6px; }
    [dir="rtl"] .pp-box ul li::before { left: auto; right: 0; }
    .pp-box .pp-note { font-size: 12px; color: #aaa; font-style: italic; border-top: 1px solid var(--border); padding-top: 12px; margin-top: 4px; }

    /* Bullet list with green circles */
    .pp-list { list-style: none; padding: 0; margin: 10px 0 0; }
    .pp-list li { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--gray); line-height: 1.7; padding: 3px 0; }
    .pp-list-dot { width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid var(--green); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
    .pp-list-dot::after { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--green); }

    /* Plain dash list */
    .pp-dash-list { list-style: none; padding: 0; margin: 10px 0 0; }
    .pp-dash-list li { font-size: 13px; color: var(--gray); line-height: 1.7; padding: 3px 0 3px 18px; position: relative; }
    [dir="rtl"] .pp-dash-list li { padding-left: 0; padding-right: 18px; }
    .pp-dash-list li::before { content: '—'; position: absolute; left: 0; color: #bbb; font-size: 11px; top: 5px; }
    [dir="rtl"] .pp-dash-list li::before { left: auto; right: 0; }

    /* Two-column use cards */
    .pp-use-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin-top: 14px; }
    .pp-use-card { padding: 20px 22px; background: var(--white); border-right: 1px solid var(--border); }
    [dir="rtl"] .pp-use-card { border-right: none; border-left: 1px solid var(--border); }
    .pp-use-card:last-child { border-right: none; border-left: none; }
    [dir="rtl"] .pp-use-card:last-child { border-left: none; }
    .pp-use-card-title { font-size: 12px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .pp-use-card-body { font-size: 12px; color: var(--gray); line-height: 1.65; }

    /* PDPL bold references */
    .pp-bold { font-weight: 700; color: var(--dark); }

    /* Contact dark card */
    .pp-contact-card { background: #1a2f1f; border-radius: 12px; padding: 28px 30px; margin-top: 14px; }
    .pp-contact-office { font-size: 13px; font-weight: 700; color: rgba(255,255,255,.9); margin-bottom: 10px; }
    .pp-contact-body { font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.75; margin-bottom: 16px; }
    .pp-contact-row { display: flex; align-items: center; gap: 8px; font-size: 12px; color: rgba(255,255,255,.7); margin-bottom: 6px; font-family: 'Courier New', monospace; }
    .pp-contact-row svg { width: 13px; height: 13px; stroke: var(--green); flex-shrink: 0; }
    .pp-contact-email { color: #5DFFA3; }

    /* ── RESPONSIVE ── */
    @media (max-width: 720px) {
        .pp-body { grid-template-columns: 1fr; }
        .pp-sidebar { display: none; }
        .pp-content { padding: 32px 0 0; border-left: none; border-right: none; }
        [dir="rtl"] .pp-content { padding: 32px 0 0; border-right: none; }
        .pp-use-grid { grid-template-columns: 1fr; }
        .pp-use-card { border-right: none; border-left: none; border-bottom: 1px solid var(--border); }
        .pp-title { font-size: 30px; }
    }
</style>
@endsection

@section('content')
<div class="pp-page">
    <div class="container">

        {{-- ── HEADER ── --}}
        <div class="pp-header">
            <div class="pp-header-inner">
                <div>
                    <h1 class="pp-title">{{ __('privacy.title') }}</h1>
                    <p class="pp-updated">{{ __('privacy.last_updated') }}</p>
                </div>
                <div class="pp-title-alt">{{ __('privacy.title_ar') }}</div>
            </div>
        </div>

        {{-- ── BODY: SIDEBAR + CONTENT ── --}}
        <div class="pp-body">

            {{-- Sidebar --}}
            <aside class="pp-sidebar">
                <div class="pp-sidebar-inner">
                    <div class="pp-sidebar-label">Sections</div>
                    <nav>
                        <a href="#collect">{{ __('privacy.nav.collect') }}</a>
                        <a href="#boq">{{ __('privacy.nav.boq') }}</a>
                        <a href="#account">{{ __('privacy.nav.account') }}</a>
                        <a href="#analytics">{{ __('privacy.nav.analytics') }}</a>
                        <a href="#use">{{ __('privacy.nav.use') }}</a>
                        <a href="#protect">{{ __('privacy.nav.protect') }}</a>
                        <a href="#sharing">{{ __('privacy.nav.sharing') }}</a>
                        <a href="#retention">{{ __('privacy.nav.retention') }}</a>
                        <a href="#rights">{{ __('privacy.nav.rights') }}</a>
                        <a href="#pdpl">{{ __('privacy.nav.pdpl') }}</a>
                        <a href="#contact-privacy">{{ __('privacy.nav.contact') }}</a>
                    </nav>
                </div>
            </aside>

            {{-- Main content --}}
            <main class="pp-content">

                {{-- 1. Information we collect --}}
                <section class="pp-section" id="collect">
                    <h2 class="pp-h2"><span class="pp-icon">§</span>{{ __('privacy.collect.h2') }}</h2>
                    <p class="pp-p">{{ __('privacy.collect.p1') }}</p>
                    <p class="pp-p">{{ __('privacy.collect.p2') }}</p>
                </section>

                {{-- 2. BOQ and project data --}}
                <section class="pp-section" id="boq">
                    <h2 class="pp-h2"><span class="pp-icon">∧</span>{{ __('privacy.boq.h2') }}</h2>
                    <div class="pp-box">
                        <p>{{ __('privacy.boq.intro') }}</p>
                        <ul>
                            <li>{{ __('privacy.boq.li1') }}</li>
                            <li>{{ __('privacy.boq.li2') }}</li>
                            <li>{{ __('privacy.boq.li3') }}</li>
                            <li>{{ __('privacy.boq.li4') }}</li>
                        </ul>
                        <p class="pp-note">{{ __('privacy.boq.note') }}</p>
                    </div>
                </section>

                {{-- 3. Account data --}}
                <section class="pp-section" id="account">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 20a7 7 0 0 1 13 0"/></svg>
                        </span>
                        {{ __('privacy.account.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.account.intro') }}</p>
                    <ul class="pp-list">
                        <li><span class="pp-list-dot"></span>{{ __('privacy.account.li1') }}</li>
                        <li><span class="pp-list-dot"></span>{{ __('privacy.account.li2') }}</li>
                        <li><span class="pp-list-dot"></span>{{ __('privacy.account.li3') }}</li>
                    </ul>
                </section>

                {{-- 4. Usage analytics --}}
                <section class="pp-section" id="analytics">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        </span>
                        {{ __('privacy.analytics.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.analytics.p') }}</p>
                </section>

                {{-- 5. How we use data --}}
                <section class="pp-section" id="use">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                        {{ __('privacy.use.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.use.intro') }}</p>
                    <div class="pp-use-grid">
                        <div class="pp-use-card">
                            <div class="pp-use-card-title">{{ __('privacy.use.op_title') }}</div>
                            <div class="pp-use-card-body">{{ __('privacy.use.op_body') }}</div>
                        </div>
                        <div class="pp-use-card">
                            <div class="pp-use-card-title">{{ __('privacy.use.assur_title') }}</div>
                            <div class="pp-use-card-body">{{ __('privacy.use.assur_body') }}</div>
                        </div>
                    </div>
                </section>

                {{-- 6. How we protect data --}}
                <section class="pp-section" id="protect">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        {{ __('privacy.protect.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.protect.intro') }}</p>
                    <ul class="pp-dash-list">
                        <li>{{ __('privacy.protect.li1') }}</li>
                        <li>{{ __('privacy.protect.li2') }}</li>
                        <li>{{ __('privacy.protect.li3') }}</li>
                        <li>{{ __('privacy.protect.li4') }}</li>
                    </ul>
                </section>

                {{-- 7. Data sharing --}}
                <section class="pp-section" id="sharing">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </span>
                        {{ __('privacy.sharing.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.sharing.intro') }}</p>
                    <ul class="pp-dash-list">
                        <li>{{ __('privacy.sharing.li1') }}</li>
                        <li>{{ __('privacy.sharing.li2') }}</li>
                        <li>{{ __('privacy.sharing.li3') }}</li>
                    </ul>
                </section>

                {{-- 8. Data retention --}}
                <section class="pp-section" id="retention">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
                        </span>
                        {{ __('privacy.retention.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.retention.p') }}</p>
                </section>

                {{-- 9. User rights --}}
                <section class="pp-section" id="rights">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        </span>
                        {{ __('privacy.rights.h2') }}
                    </h2>
                    <p class="pp-p">{{ __('privacy.rights.intro') }}</p>
                    <ul class="pp-dash-list">
                        <li>{{ __('privacy.rights.li1') }}</li>
                        <li>{{ __('privacy.rights.li2') }}</li>
                        <li>{{ __('privacy.rights.li3') }}</li>
                        <li>{{ __('privacy.rights.li4') }}</li>
                    </ul>
                </section>

                {{-- 10. PDPL and GDPR alignment --}}
                <section class="pp-section" id="pdpl">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg>
                        </span>
                        {{ __('privacy.pdpl.h2') }}
                    </h2>
                    <p class="pp-p">
                        {!! str_replace(
                            [':pdpl', ':gdpr'],
                            [
                                '<strong class="pp-bold">' . __('privacy.pdpl.pdpl') . '</strong>',
                                '<strong class="pp-bold">' . __('privacy.pdpl.gdpr') . '</strong>',
                            ],
                            __('privacy.pdpl.p')
                        ) !!}
                    </p>
                </section>

                {{-- 11. Contact for privacy requests --}}
                <section class="pp-section" id="contact-privacy">
                    <h2 class="pp-h2">
                        <span class="pp-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        {{ __('privacy.contact.h2') }}
                    </h2>
                    <div class="pp-contact-card">
                        <div class="pp-contact-office">{{ __('privacy.contact.office') }}</div>
                        <div class="pp-contact-body">{{ __('privacy.contact.body') }}</div>
                        <div class="pp-contact-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span class="pp-contact-email">{{ __('privacy.contact.email') }}</span>
                        </div>
                        <div class="pp-contact-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>{{ __('privacy.contact.location') }}</span>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var sections = document.querySelectorAll('.pp-section');
    var navLinks = document.querySelectorAll('.pp-sidebar nav a');
    if (!sections.length || !navLinks.length) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                navLinks.forEach(function (a) { a.classList.remove('is-active'); });
                var active = document.querySelector('.pp-sidebar nav a[href="#' + entry.target.id + '"]');
                if (active) active.classList.add('is-active');
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px' });

    sections.forEach(function (s) { observer.observe(s); });
})();
</script>
@endsection
