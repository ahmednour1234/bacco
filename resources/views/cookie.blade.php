@extends('layouts.app')

@section('title', __('cookie.hero.title'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<style>
/* ── COOKIE PAGE ────────────────────────────────────────────────────────── */
.cookie-page {
    font-family: 'Cairo','Inter',sans-serif;
    color: var(--dark);
    background: #fff;
    max-width: 780px;
    margin: 0 auto;
    padding: 56px 32px 100px;
}
@media (max-width: 600px) { .cookie-page { padding: 36px 18px 70px; } }

/* ── HERO ──────────────────────────────────────────────────────────────── */
.cookie-hero { margin-bottom: 12px; }
.cookie-h1 {
    font-size: clamp(30px, 5vw, 46px);
    font-weight: 800;
    color: var(--dark);
    line-height: 1.1;
    margin-bottom: 6px;
}
.cookie-ar {
    font-size: clamp(16px, 2.5vw, 24px);
    font-weight: 700;
    color: var(--green);
    display: block;
    margin-bottom: 14px;
}
.cookie-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    color: #aaa;
    margin-bottom: 40px;
}
.cookie-meta svg { width: 13px; height: 13px; flex-shrink: 0; }
.cookie-divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 0 0 44px;
}

/* ── SECTION CARD ──────────────────────────────────────────────────────── */
.cookie-section {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 26px 28px 28px;
    margin-bottom: 22px;
    scroll-margin-top: 88px;
}
@media (max-width: 600px) { .cookie-section { padding: 20px 16px; } }

.cookie-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 17px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 14px;
}
.cookie-section-title svg {
    width: 18px;
    height: 18px;
    color: var(--green);
    flex-shrink: 0;
}

.cookie-section p {
    font-size: 13.5px;
    color: var(--gray);
    line-height: 1.8;
    margin-bottom: 12px;
}
.cookie-section p:last-child { margin-bottom: 0; }

/* ── COOKIE TABLE ──────────────────────────────────────────────────────── */
.cookie-table {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 8px;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    font-size: 13px;
    margin-top: 16px;
}
.cookie-table thead tr { background: #f7f7f5; }
.cookie-table th {
    text-align: left;
    padding: 10px 14px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--gray);
    letter-spacing: .04em;
    border-bottom: 1px solid var(--border);
}
[dir=rtl] .cookie-table th { text-align: right; }
.cookie-table td {
    padding: 12px 14px;
    color: var(--dark);
    border-bottom: 1px solid #f0f0f0;
    vertical-align: top;
}
.cookie-table tr:last-child td { border-bottom: none; }
.cookie-table td:first-child {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: var(--green);
    font-weight: 600;
    white-space: nowrap;
}
.cookie-table td:last-child { color: var(--gray); font-size: 12.5px; }

/* ── BULLET LIST ───────────────────────────────────────────────────────── */
.cookie-bullets {
    list-style: none;
    padding: 0;
    margin: 12px 0 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.cookie-bullets li {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    font-size: 13.5px;
    color: var(--gray);
    line-height: 1.6;
}
.cookie-bullets li::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--green);
    margin-top: 7px;
    flex-shrink: 0;
}

/* ── PREFERENCE BOX ────────────────────────────────────────────────────── */
.cookie-pref-box {
    background: var(--green-light);
    border: 1px solid rgba(0,106,59,.15);
    border-radius: 7px;
    padding: 14px 16px;
    margin-top: 14px;
}
.cookie-pref-box-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 4px;
}
.cookie-pref-box p {
    font-size: 12.5px;
    color: var(--gray);
    margin: 0;
    line-height: 1.65;
}

/* ── BROWSER TAGS ──────────────────────────────────────────────────────── */
.cookie-browser-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
}
.cookie-browser-tag {
    display: flex;
    align-items: center;
    gap: 7px;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--dark);
    text-decoration: none;
    transition: border-color .2s, color .2s, background .2s;
}
.cookie-browser-tag svg { width: 14px; height: 14px; color: var(--gray); }
.cookie-browser-tag:hover {
    border-color: var(--green);
    color: var(--green);
    background: rgba(0,106,59,.04);
}

/* ── CONTACT SECTION ───────────────────────────────────────────────────── */
.cookie-contact-text {
    font-size: 13.5px;
    color: var(--gray);
    line-height: 1.8;
    margin-bottom: 18px;
}
.cookie-contact-text a {
    color: var(--green);
    font-weight: 600;
    text-decoration: underline;
}
.cookie-contact-btn {
    display: inline-block;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 10px 22px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--dark);
    text-decoration: none;
    transition: border-color .2s, color .2s;
}
.cookie-contact-btn:hover {
    border-color: var(--green);
    color: var(--green);
}
</style>

<div class="cookie-page">

    {{-- ── HERO ──────────────────────────────────────────────────────────── --}}
    <div class="cookie-hero">
        <h1 class="cookie-h1">{{ __('cookie.hero.title') }}</h1>
        <span class="cookie-ar">{{ __('cookie.hero.title_ar') }}</span>
        <div class="cookie-meta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            {{ __('cookie.hero.updated') }}
        </div>
    </div>
    <hr class="cookie-divider">

    {{-- ── §1 WHAT COOKIES ARE ────────────────────────────────────────── --}}
    <div class="cookie-section" id="what">
        <div class="cookie-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ __('cookie.what.title') }}
        </div>
        <p>{{ __('cookie.what.p1') }}</p>
        <p>{{ __('cookie.what.p2') }}</p>
    </div>

    {{-- ── §2 ESSENTIAL COOKIES ───────────────────────────────────────── --}}
    <div class="cookie-section" id="essential">
        <div class="cookie-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            {{ __('cookie.essential.title') }}
        </div>
        <p>{{ __('cookie.essential.desc') }}</p>
        <table class="cookie-table">
            <thead>
                <tr>
                    <th>{{ __('cookie.essential.col1') }}</th>
                    <th>{{ __('cookie.essential.col2') }}</th>
                    <th>{{ __('cookie.essential.col3') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('cookie.essential.r1_name') }}</td>
                    <td>{{ __('cookie.essential.r1_purp') }}</td>
                    <td>{{ __('cookie.essential.r1_dur') }}</td>
                </tr>
                <tr>
                    <td>{{ __('cookie.essential.r2_name') }}</td>
                    <td>{{ __('cookie.essential.r2_purp') }}</td>
                    <td>{{ __('cookie.essential.r2_dur') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ── §3 ANALYTICS COOKIES ───────────────────────────────────────── --}}
    <div class="cookie-section" id="analytics">
        <div class="cookie-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            {{ __('cookie.analytics.title') }}
        </div>
        <p>{{ __('cookie.analytics.desc') }}</p>
        <ul class="cookie-bullets">
            <li>{{ __('cookie.analytics.b1') }}</li>
            <li>{{ __('cookie.analytics.b2') }}</li>
            <li>{{ __('cookie.analytics.b3') }}</li>
        </ul>
    </div>

    {{-- ── §4 PREFERENCE COOKIES ──────────────────────────────────────── --}}
    <div class="cookie-section" id="preference">
        <div class="cookie-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                <path d="M15.54 8.46a5 5 0 0 1 0 7.07M8.46 8.46a5 5 0 0 0 0 7.07"/>
            </svg>
            {{ __('cookie.preference.title') }}
        </div>
        <p>{{ __('cookie.preference.desc') }}</p>
        <div class="cookie-pref-box">
            <div class="cookie-pref-box-title">{{ __('cookie.preference.pref_title') }}</div>
            <p>{{ __('cookie.preference.pref_desc') }}</p>
        </div>
    </div>

    {{-- ── §5 MANAGING COOKIES ────────────────────────────────────────── --}}
    <div class="cookie-section" id="manage">
        <div class="cookie-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
                <line x1="4" y1="18" x2="20" y2="18"/>
                <circle cx="2" cy="6" r="2" fill="currentColor" stroke="none"/>
                <circle cx="22" cy="12" r="2" fill="currentColor" stroke="none"/>
                <circle cx="2" cy="18" r="2" fill="currentColor" stroke="none"/>
            </svg>
            {{ __('cookie.manage.title') }}
        </div>
        <p>{{ __('cookie.manage.desc') }}</p>
        <div class="cookie-browser-tags">
            <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer" class="cookie-browser-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                {{ __('cookie.manage.btn1') }}
            </a>
            <a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471" target="_blank" rel="noopener noreferrer" class="cookie-browser-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                {{ __('cookie.manage.btn2') }}
            </a>
            <a href="https://support.microsoft.com/en-us/windows/manage-cookies-in-microsoft-edge" target="_blank" rel="noopener noreferrer" class="cookie-browser-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                {{ __('cookie.manage.btn3') }}
            </a>
        </div>
    </div>

    {{-- ── §6 CONTACT ─────────────────────────────────────────────────── --}}
    <div class="cookie-section" id="contact">
        <div class="cookie-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            {{ __('cookie.contact.title') }}
        </div>
        <p class="cookie-contact-text">
            {{ __('cookie.contact.desc') }}
            <a href="mailto:{{ __('cookie.contact.email') }}">{{ __('cookie.contact.email') }}</a>
            {{ __('cookie.contact.desc2') }}
        </p>
        <a href="{{ route('contact') }}" class="cookie-contact-btn">{{ __('cookie.contact.btn') }}</a>
    </div>

</div>
@endsection
