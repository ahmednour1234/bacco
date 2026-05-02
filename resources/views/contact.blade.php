@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', __('contact.hero.h1') . ' — QIMTA')

@section('nav-cta')
    <a href="#" class="btn-demo">{{ __('contact.nav.get_demo') }}</a>
@endsection

@section('mobile-cta')
    <a href="#" class="btn-demo">{{ __('contact.nav.get_demo') }}</a>
@endsection

@section('styles')
<style>
    :root { --gray:#666; }
    .container { max-width: 1080px; padding: 0 32px; }

    /* ── HERO ── */
    .contact-hero { background: var(--cream); padding: 56px 0 64px; border-bottom: 1px solid var(--border); }
    .contact-hero-inner { display: flex; align-items: flex-start; justify-content: space-between; gap: 32px; }
    .hero-left { flex: 1; }
    .hero-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 16px; display: block; }
    [dir="rtl"] .hero-label { letter-spacing: 0; }
    .contact-h1 { font-size: 48px; font-weight: 800; color: var(--dark); line-height: 1.05; letter-spacing: -2px; margin-bottom: 12px; }
    [dir="rtl"] .contact-h1 { letter-spacing: 0; }
    .hero-pill { width: 52px; height: 8px; border-radius: 50px; background: #d1d5db; margin-bottom: 20px; }
    .contact-hero-sub { font-size: 14px; color: var(--gray); line-height: 1.8; max-width: 480px; }
    .hero-status { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #555; white-space: nowrap; padding-top: 6px; }
    [dir="rtl"] .hero-status { letter-spacing: 0; }
    .hero-status-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); flex-shrink: 0; }

    /* ── INQUIRY TYPE CARDS ── */
    .inquiry-section { background: var(--white); padding: 64px 0; border-bottom: 1px solid var(--border); }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--green); margin-bottom: 28px; display: block; }
    [dir="rtl"] .section-label { letter-spacing: 0; }
    .inquiry-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .inq-card { border: 1px solid var(--border); border-radius: 12px; padding: 24px 22px; cursor: pointer; transition: border-color .2s, box-shadow .2s; background: var(--white); }
    .inq-card:hover { border-color: var(--green); box-shadow: 0 0 0 3px rgba(0,106,59,.07); }
    .inq-icon { width: 36px; height: 36px; border-radius: 8px; background: #f0fdf4; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    .inq-icon svg { width: 18px; height: 18px; stroke: var(--green); }
    .inq-title { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
    .inq-desc { font-size: 12px; color: var(--gray); line-height: 1.65; }

    /* ── FORM + CHANNELS ── */
    .form-section { background: var(--cream); padding: 64px 0; border-bottom: 1px solid var(--border); }
    .form-card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; display: grid; grid-template-columns: 1fr 360px; gap: 0; overflow: hidden; }
    .form-left { padding: 40px 40px 40px 40px; border-right: 1px solid var(--border); }
    [dir="rtl"] .form-left { border-right: none; border-left: 1px solid var(--border); }
    .form-title { font-size: 18px; font-weight: 800; color: var(--dark); margin-bottom: 28px; letter-spacing: -0.5px; }
    [dir="rtl"] .form-title { letter-spacing: 0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #888; }
    [dir="rtl"] .form-group label { letter-spacing: 0; }
    .form-group input,
    .form-group select,
    .form-group textarea { width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; font-size: 13px; font-family: inherit; color: var(--dark); background: var(--white); outline: none; transition: border-color .2s, box-shadow .2s; box-sizing: border-box; }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(0,106,59,.08); }
    .form-group textarea { resize: vertical; min-height: 110px; }
    .phone-wrap { display: flex; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; transition: border-color .2s, box-shadow .2s; }
    .phone-wrap:focus-within { border-color: var(--green); box-shadow: 0 0 0 3px rgba(0,106,59,.08); }
    .phone-prefix { padding: 10px 14px; background: #f9fafb; border-right: 1px solid var(--border); font-size: 13px; color: var(--dark); white-space: nowrap; font-weight: 600; }
    [dir="rtl"] .phone-prefix { border-right: none; border-left: 1px solid var(--border); }
    .phone-wrap input { border: none; border-radius: 0; box-shadow: none !important; outline: none; }
    .phone-wrap input:focus { border: none; box-shadow: none !important; }
    .btn-submit { display: inline-flex; align-items: center; gap: 8px; background: var(--green); color: var(--white); border: none; border-radius: 8px; padding: 13px 28px; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; transition: background .2s; margin-top: 8px; font-family: inherit; }
    [dir="rtl"] .btn-submit { letter-spacing: 0; }
    .btn-submit:hover { background: #005530; }
    .btn-submit svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; }

    /* channels column */
    .form-right { padding: 40px 32px; display: flex; flex-direction: column; gap: 16px; }
    .channels-title { font-size: 16px; font-weight: 800; color: var(--dark); letter-spacing: -0.4px; margin-bottom: 4px; }
    [dir="rtl"] .channels-title { letter-spacing: 0; }
    .channels-sub { font-size: 12px; color: var(--gray); line-height: 1.65; margin-bottom: 8px; }
    .channel-item { border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; }
    .channel-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 4px; }
    [dir="rtl"] .channel-label { letter-spacing: 0; }
    .channel-email { font-size: 13px; font-weight: 600; color: var(--green); text-decoration: none; word-break: break-all; }
    .channel-email:hover { text-decoration: underline; }
    .region-card { border: 1px solid #d1fae5; border-radius: 10px; padding: 16px; background: #f0fdf4; margin-top: auto; }
    .region-badge { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--green); margin-bottom: 8px; }
    [dir="rtl"] .region-badge { letter-spacing: 0; }
    .region-quote { font-size: 13px; font-weight: 700; color: var(--dark); margin-bottom: 8px; line-height: 1.4; }
    .region-body { font-size: 12px; color: var(--gray); line-height: 1.65; }

    /* ── LOCATIONS ── */
    .locations-section { background: var(--white); padding: 72px 0; border-bottom: 1px solid var(--border); }
    .locations-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; }
    .loc-visual { position: relative; border-radius: 20px; overflow: hidden; height: 340px; background: #e5e7eb; }
    .loc-visual-split { display: flex; height: 100%; }
    .loc-map-phone { flex: 0 0 52%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; padding: 24px 16px; }
    .loc-phone-mock { width: 130px; background: var(--white); border-radius: 22px; box-shadow: 0 8px 32px rgba(0,0,0,.18); overflow: hidden; border: 3px solid #1a1a1a; position: relative; }
    .loc-phone-screen { background: #e8f5e9; height: 200px; position: relative; display: flex; align-items: center; justify-content: center; }
    .loc-phone-screen svg { width: 90px; height: 90px; }
    .loc-phone-bar { background: #1a1a1a; height: 24px; display: flex; align-items: center; justify-content: center; gap: 4px; }
    .loc-phone-dot { width: 6px; height: 6px; border-radius: 50%; background: #555; }
    .loc-phone-notch { width: 36px; height: 5px; border-radius: 3px; background: #333; }
    .loc-city { flex: 1; position: relative; background: linear-gradient(135deg, #1a2f4e 0%, #2d5016 50%, #1a3a1a 100%); display: flex; align-items: flex-end; padding: 20px 18px; }
    .loc-city-img { position: absolute; inset: 0; object-fit: cover; width: 100%; height: 100%; opacity: .7; }
    .loc-pin { position: absolute; top: 30%; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 2px; z-index: 2; }
    .loc-pin-icon { width: 36px; height: 36px; background: var(--green); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,106,59,.5); }
    .loc-pin-inner { width: 14px; height: 14px; background: var(--white); border-radius: 50%; transform: rotate(45deg); display: flex; align-items: center; justify-content: center; font-size: 7px; font-weight: 900; color: var(--green); }
    .loc-pin-label { background: var(--white); border-radius: 4px; padding: 3px 8px; font-size: 9px; font-weight: 700; color: var(--dark); box-shadow: 0 2px 8px rgba(0,0,0,.15); white-space: nowrap; }
    .loc-badge { position: absolute; bottom: 14px; left: 14px; background: var(--white); border-radius: 8px; padding: 7px 12px; display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--dark); box-shadow: 0 4px 16px rgba(0,0,0,.12); z-index: 3; }
    [dir="rtl"] .loc-badge { letter-spacing: 0; left: auto; right: 14px; }
    .loc-badge-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); }

    .loc-content { }
    .loc-section-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 14px; display: block; }
    [dir="rtl"] .loc-section-label { letter-spacing: 0; }
    .loc-h2 { font-size: 34px; font-weight: 800; color: var(--dark); letter-spacing: -1.2px; margin-bottom: 28px; line-height: 1.1; }
    [dir="rtl"] .loc-h2 { letter-spacing: 0; }
    .office-cards { display: flex; flex-direction: column; gap: 16px; }
    .office-card { border: 1px dashed #c8e6c9; border-radius: 14px; padding: 22px 24px; background: #fafff9; }
    .office-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .office-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); flex-shrink: 0; }
    .office-title { font-size: 15px; font-weight: 800; color: var(--dark); }
    .office-address { font-size: 13px; color: var(--gray); line-height: 1.75; margin-bottom: 14px; }
    .office-dir { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--green); text-decoration: none; border: 1px solid #86efac; border-radius: 6px; padding: 7px 14px; transition: background .2s, color .2s; }
    [dir="rtl"] .office-dir { letter-spacing: 0; }
    .office-dir:hover { background: var(--green); color: var(--white); border-color: var(--green); }
    .office-dir svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.2; }

    /* ── CTA BANNER ── */
    .cta-banner { background: var(--green); padding: 64px 0; }
    .cta-inner { text-align: center; }
    .cta-h2 { font-size: 34px; font-weight: 800; color: var(--white); letter-spacing: -1px; margin-bottom: 14px; }
    [dir="rtl"] .cta-h2 { letter-spacing: 0; }
    .cta-sub { font-size: 14px; color: rgba(255,255,255,.75); line-height: 1.75; max-width: 500px; margin: 0 auto 28px; }
    .cta-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--white); color: var(--green); border-radius: 8px; padding: 14px 30px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-decoration: none; transition: opacity .2s; }
    .cta-btn:hover { opacity: .9; }
    .cta-btn svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2.2; }

    /* ── RESPONSIVE ── */
    @media (max-width: 860px) {
        .contact-hero-inner { flex-direction: column; gap: 20px; }
        .hero-status { align-self: flex-start; }
        .inquiry-grid { grid-template-columns: 1fr 1fr; }
        .form-card { grid-template-columns: 1fr; }
        .form-left { border-right: none; border-bottom: 1px solid var(--border); }
        [dir="rtl"] .form-left { border-left: none; border-bottom: 1px solid var(--border); }
        .locations-inner { grid-template-columns: 1fr; }
        .loc-visual { height: 260px; }
    }
    @media (max-width: 580px) {
        .contact-h1 { font-size: 34px; }
        .inquiry-grid { grid-template-columns: 1fr; }
        .form-row { grid-template-columns: 1fr; }
        .loc-h2 { font-size: 26px; }
        .cta-h2 { font-size: 26px; }
    }
</style>
@endsection

@section('content')

{{-- ── HERO ── --}}
<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-inner">
            <div class="hero-left">
                <span class="hero-label">{{ __('contact.hero.label') }}</span>
                <h1 class="contact-h1">{{ __('contact.hero.h1') }}</h1>
                <div class="hero-pill"></div>
                <p class="contact-hero-sub">{{ __('contact.hero.sub') }}</p>
            </div>
            <div class="hero-status">
                <span class="hero-status-dot"></span>
                {{ __('contact.hero.status') }}
            </div>
        </div>
    </div>
</section>

{{-- ── INQUIRY TYPE ── --}}
<section class="inquiry-section">
    <div class="container">
        <span class="section-label">{{ __('contact.inquiry.label') }}</span>
        <div class="inquiry-grid">

            <div class="inq-card">
                <div class="inq-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 17H7A5 5 0 0 1 7 7h2"/><path d="M15 7h2a5 5 0 0 1 0 10h-2"/><line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <div class="inq-title">{{ __('contact.inquiry.boq') }}</div>
                <div class="inq-desc">{{ __('contact.inquiry.boq_sub') }}</div>
            </div>

            <div class="inq-card">
                <div class="inq-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2"/><path d="M8 7V5a2 2 0 0 0-4 0v2"/>
                    </svg>
                </div>
                <div class="inq-title">{{ __('contact.inquiry.brand') }}</div>
                <div class="inq-desc">{{ __('contact.inquiry.brand_sub') }}</div>
            </div>

            <div class="inq-card">
                <div class="inq-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </div>
                <div class="inq-title">{{ __('contact.inquiry.enterprise') }}</div>
                <div class="inq-desc">{{ __('contact.inquiry.enterprise_sub') }}</div>
            </div>

            <div class="inq-card">
                <div class="inq-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                    </svg>
                </div>
                <div class="inq-title">{{ __('contact.inquiry.support') }}</div>
                <div class="inq-desc">{{ __('contact.inquiry.support_sub') }}</div>
            </div>

            <div class="inq-card">
                <div class="inq-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="inq-title">{{ __('contact.inquiry.partnership') }}</div>
                <div class="inq-desc">{{ __('contact.inquiry.partnership_sub') }}</div>
            </div>

            <div class="inq-card">
                <div class="inq-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v2"/><path d="M2 6h6"/><path d="M2 10h6"/><path d="M2 14h6"/>
                    </svg>
                </div>
                <div class="inq-title">{{ __('contact.inquiry.press') }}</div>
                <div class="inq-desc">{{ __('contact.inquiry.press_sub') }}</div>
            </div>

        </div>
    </div>
</section>

{{-- ── FORM + CHANNELS ── --}}
<section class="form-section">
    <div class="container">
        <div class="form-card">

            {{-- Left: Form --}}
            <div class="form-left">
                <div class="form-title">{{ __('contact.form.title') }}</div>
                <form action="#" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label>{{ __('contact.form.name') }}</label>
                            <input type="text" name="name" placeholder="{{ __('contact.form.name_ph') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('contact.form.email') }}</label>
                            <input type="email" name="email" placeholder="{{ __('contact.form.email_ph') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('contact.form.phone') }}</label>
                        <div class="phone-wrap">
                            <span class="phone-prefix">+966</span>
                            <input type="tel" name="phone" placeholder="5X XXX XXXX">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>{{ __('contact.form.company') }}</label>
                            <input type="text" name="company" placeholder="{{ __('contact.form.company_ph') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('contact.form.role') }}</label>
                            <input type="text" name="role" placeholder="{{ __('contact.form.role_ph') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('contact.form.inquiry_type') }}</label>
                        <select name="inquiry_type">
                            <option value="">{{ __('contact.form.type_boq') }}</option>
                            <option value="brand">{{ __('contact.form.type_brand') }}</option>
                            <option value="enterprise">{{ __('contact.form.type_enterprise') }}</option>
                            <option value="support">{{ __('contact.form.type_support') }}</option>
                            <option value="partner">{{ __('contact.form.type_partner') }}</option>
                            <option value="press">{{ __('contact.form.type_press') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('contact.form.message') }}</label>
                        <textarea name="message" placeholder="{{ __('contact.form.message_ph') }}"></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        {{ __('contact.form.submit') }}
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>
            </div>

            {{-- Right: Direct Channels --}}
            <div class="form-right">
                <div>
                    <div class="channels-title">{{ __('contact.channels.title') }}</div>
                    <div class="channels-sub">{{ __('contact.channels.sub') }}</div>
                </div>

                <div class="channel-item">
                    <div class="channel-label">{{ __('contact.channels.sales') }}</div>
                    <a href="mailto:sales@qimta.com" class="channel-email">sales@qimta.com</a>
                </div>
                <div class="channel-item">
                    <div class="channel-label">{{ __('contact.channels.brands') }}</div>
                    <a href="mailto:brands@qimta.com" class="channel-email">brands@qimta.com</a>
                </div>
                <div class="channel-item">
                    <div class="channel-label">{{ __('contact.channels.support') }}</div>
                    <a href="mailto:support@qimta.com" class="channel-email">support@qimta.com</a>
                </div>
                <div class="channel-item">
                    <div class="channel-label">{{ __('contact.channels.press') }}</div>
                    <a href="mailto:press@qimta.com" class="channel-email">press@qimta.com</a>
                </div>

                <div class="region-card">
                    <div class="region-badge">{{ __('contact.region.title') }}</div>
                    <div class="region-quote">{{ __('contact.region.quote') }}</div>
                    <div class="region-body">{{ __('contact.region.body') }}</div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── LOCATIONS ── --}}
<section class="locations-section">
    <div class="container">
        <div class="locations-inner">

            {{-- Visual --}}
            <div class="loc-visual">
                <div class="loc-visual-split">

                    {{-- Phone mock with map --}}
                    <div class="loc-map-phone">
                        <div class="loc-phone-mock">
                            <div class="loc-phone-bar">
                                <div class="loc-phone-notch"></div>
                            </div>
                            <div class="loc-phone-screen">
                                {{-- Mini map SVG --}}
                                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100" height="100" fill="#e8f5e9"/>
                                    {{-- Roads --}}
                                    <rect x="0" y="45" width="100" height="8" fill="#c8e6c9"/>
                                    <rect x="45" y="0" width="8" height="100" fill="#c8e6c9"/>
                                    <rect x="20" y="20" width="25" height="20" rx="2" fill="#a5d6a7" opacity=".6"/>
                                    <rect x="55" y="30" width="20" height="15" rx="2" fill="#a5d6a7" opacity=".5"/>
                                    <rect x="20" y="60" width="20" height="18" rx="2" fill="#a5d6a7" opacity=".5"/>
                                    <rect x="60" y="60" width="22" height="16" rx="2" fill="#a5d6a7" opacity=".4"/>
                                    {{-- Pin --}}
                                    <circle cx="49" cy="44" r="7" fill="#006A3B"/>
                                    <circle cx="49" cy="44" r="3.5" fill="white"/>
                                    <polygon points="49,55 44,44 54,44" fill="#006A3B"/>
                                    {{-- Label --}}
                                    <rect x="20" y="68" width="60" height="14" rx="4" fill="white" opacity=".9"/>
                                    <text x="50" y="78" text-anchor="middle" font-size="6" fill="#333" font-weight="bold">DIFC - Gate Village</text>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- City aerial --}}
                    <div class="loc-city">
                        <div class="loc-pin">
                            <div class="loc-pin-icon">
                                <div class="loc-pin-inner">Q</div>
                            </div>
                            <div class="loc-pin-label">DIFC - Gate Village</div>
                        </div>
                        {{-- SVG skyline --}}
                        <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.25" viewBox="0 0 200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                            <rect width="200" height="120" fill="#0d2a1a"/>
                            <rect x="10" y="40" width="12" height="80" fill="#1a4a2a"/>
                            <rect x="26" y="20" width="10" height="100" fill="#1a5a30"/>
                            <rect x="40" y="10" width="14" height="110" fill="#1a6a3a"/>
                            <rect x="58" y="30" width="10" height="90" fill="#1a4a2a"/>
                            <rect x="72" y="5" width="16" height="115" fill="#2a7a4a"/>
                            <rect x="92" y="25" width="12" height="95" fill="#1a5a30"/>
                            <rect x="108" y="15" width="10" height="105" fill="#2a6a3a"/>
                            <rect x="122" y="35" width="14" height="85" fill="#1a4a2a"/>
                            <rect x="140" y="20" width="12" height="100" fill="#1a5a30"/>
                            <rect x="156" y="8" width="10" height="112" fill="#2a7a4a"/>
                            <rect x="170" y="30" width="14" height="90" fill="#1a4a2a"/>
                            <rect x="0" y="90" width="200" height="30" fill="#0a3a1a" opacity=".8"/>
                        </svg>
                    </div>

                </div>

                {{-- LIVE badge --}}
                <div class="loc-badge">
                    <span class="loc-badge-dot"></span>
                    {{ __('contact.locations.badge') }}
                </div>
            </div>

            {{-- Office cards --}}
            <div class="loc-content">
                <span class="loc-section-label">{{ __('contact.locations.label') }}</span>
                <h2 class="loc-h2">{{ __('contact.locations.h2') }}</h2>

                <div class="office-cards">
                    <div class="office-card">
                        <div class="office-card-header">
                            <span class="office-dot"></span>
                            <span class="office-title">{{ __('contact.locations.dubai_title') }}</span>
                        </div>
                        <div class="office-address">
                            {{ __('contact.locations.dubai_line1') }}<br>
                            {{ __('contact.locations.dubai_line2') }}<br>
                            {{ __('contact.locations.dubai_line3') }}
                        </div>
                        <a href="https://maps.google.com/?q=DIFC+Gate+Village+Dubai" target="_blank" rel="noopener" class="office-dir">
                            {{ __('contact.locations.dubai_dir') }}
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>

                    <div class="office-card">
                        <div class="office-card-header">
                            <span class="office-dot"></span>
                            <span class="office-title">{{ __('contact.locations.riyadh_title') }}</span>
                        </div>
                        <div class="office-address">
                            {{ __('contact.locations.riyadh_line1') }}<br>
                            {{ __('contact.locations.riyadh_line2') }}<br>
                            {{ __('contact.locations.riyadh_line3') }}
                        </div>
                        <a href="https://maps.google.com/?q=Al+Ziya+Riyadh+Saudi+Arabia" target="_blank" rel="noopener" class="office-dir">
                            {{ __('contact.locations.riyadh_dir') }}
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── CTA BANNER ── --}}
<section class="cta-banner">
    <div class="container">
        <div class="cta-inner">
            <h2 class="cta-h2">{{ __('contact.cta.h2') }}</h2>
            <p class="cta-sub">{{ __('contact.cta.sub') }}</p>
            <a href="#" class="cta-btn">
                {{ __('contact.cta.btn') }}
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
