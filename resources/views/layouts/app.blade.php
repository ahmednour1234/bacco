@php
    $locale = app()->getLocale();
    $isAr   = $locale === 'ar';
    $dir    = $isAr ? 'rtl' : 'ltr';
    $switchLocale = $isAr ? 'en' : 'ar';
    $switchLabel  = $isAr ? 'EN' : 'AR';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'QIMTA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Manrope:wght@400;500;600;700;800&family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --green: #006A3B;
            --green-btn: #006A3B;
            --green-light: rgba(0,106,59,0.08);
            --green-sage: #BDCABD;
            --dark: #111111;
            --cream: #f5f4f0;
            --white: #ffffff;
            --gray-text: #666666;
            --border: #e0e0e0;
        }
        html { scroll-behavior: smooth; }
        body { font-family: 'Cairo', 'Inter', sans-serif; background: var(--white); color: var(--dark); font-size: 15px; line-height: 1.6; overflow-x: hidden; }
        [dir="rtl"] { font-family: 'Cairo', sans-serif; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        img { max-width: 100%; display: block; }
        .container { max-width: 1120px; margin: 0 auto; padding: 0 28px; }

        /* ── NAV ── */
        .nav { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.07); box-shadow: 0 1px 0 rgba(0,0,0,0.04); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 68px; gap: 16px; }
        .nav-logo { display: flex; align-items: center; gap: 8px; font-size: 21px; font-weight: 900; letter-spacing: -0.8px; color: var(--green); flex-shrink: 0; text-decoration: none; }
        .nav-logo-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); opacity: 0.5; margin-bottom: -6px; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a { position: relative; font-size: 14px; font-weight: 500; color: #444; padding: 6px 12px; border-radius: 6px; transition: color .2s, background .2s; white-space: nowrap; text-decoration: none; }
        .nav-links a::after { content: ''; position: absolute; bottom: 2px; left: 12px; right: 12px; height: 2px; border-radius: 2px; background: var(--green); transform: scaleX(0); transform-origin: center; transition: transform .25s ease; }
        .nav-links a:hover, .nav-links a.active { color: var(--green); }
        .nav-links a:hover::after, .nav-links a.active::after { transform: scaleX(1); }
        .nav-links a.active { font-weight: 700; }
        /* ── MORE DROPDOWN ── */
        .nav-more { position: relative; display: inline-flex; align-items: center; }
        .nav-more-btn { display: inline-flex; align-items: center; gap: 5px; font-size: 14px; font-weight: 500; color: #444; padding: 6px 12px; border-radius: 6px; cursor: pointer; background: none; border: none; font-family: inherit; transition: color .2s; white-space: nowrap; }
        .nav-more-btn:hover, .nav-more.open .nav-more-btn { color: var(--green); }
        .nav-more-btn svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.5; transition: transform .2s; flex-shrink: 0; }
        .nav-more.open .nav-more-btn svg { transform: rotate(180deg); }
        .nav-more-dropdown { display: none; position: absolute; top: calc(100% + 8px); right: 0; background: var(--white); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.06); padding: 6px; min-width: 210px; z-index: 200; }
        .nav-more.open .nav-more-dropdown { display: block; animation: dropFade .15s ease; }
        @keyframes dropFade { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
        .nav-more-dropdown a { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; color: #333; padding: 10px 14px; border-radius: 8px; text-decoration: none; transition: background .15s, color .15s; white-space: nowrap; }
        .nav-more-dropdown a:hover, .nav-more-dropdown a.active { background: #f0fdf4; color: var(--green); }
        .nav-more-dropdown a.active { font-weight: 700; }
        .nav-more-dropdown a svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; flex-shrink: 0; opacity: .6; }
        .nav-more-sep { height: 1px; background: var(--border); margin: 4px 8px; }
        .nav-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; border: none; white-space: nowrap; text-decoration: none; }
        .btn-ghost { background: transparent; color: #444; padding: 9px 14px; }
        .btn-ghost:hover { background: rgba(0,0,0,0.05); color: var(--dark); }
        .btn-primary { background: var(--green); color: var(--white); box-shadow: 0 1px 3px rgba(0,106,59,0.25), 0 4px 12px rgba(0,106,59,0.15); }
        .btn-primary:hover { background: #005a32; transform: translateY(-1px); }
        .btn-dark { background: var(--dark); color: var(--white); }
        .btn-dark:hover { background: #333; }
        .btn-outline { background: transparent; color: var(--dark); border: 1.5px solid var(--dark); }
        .btn-outline:hover { background: var(--dark); color: var(--white); }
        .btn-outline-white { background: transparent; color: var(--white); border: 1.5px solid rgba(255,255,255,0.5); }
        .btn-outline-white:hover { background: rgba(255,255,255,.1); border-color: var(--white); }
        .btn-lg { padding: 14px 28px; font-size: 15px; border-radius: 10px; }
        .btn-nav-cta { background: var(--green); color: var(--white); padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(0,106,59,0.2), 0 4px 14px rgba(0,106,59,0.15); transition: all .2s; text-decoration: none; }
        .btn-nav-cta:hover { background: #005a32; transform: translateY(-1px); }
        .btn-nav-cta .cta-badge { background: rgba(255,255,255,0.2); font-size: 10px; font-weight: 800; letter-spacing: 0.5px; padding: 2px 7px; border-radius: 20px; text-transform: uppercase; }
        .btn-nav-cta svg { opacity: 0.85; transition: transform .2s; }
        .btn-nav-cta:hover svg { transform: translateX(3px); }
        .btn-demo { background: var(--green); color: var(--white); font-size: 13.5px; font-weight: 700; padding: 9px 18px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,106,59,0.2), 0 4px 12px rgba(0,106,59,0.15); transition: all .2s; display: inline-flex; align-items: center; }
        .btn-demo:hover { background: #005a32; transform: translateY(-1px); }
        .lang-btn { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; border: 1.5px solid var(--border); color: #555; background: var(--white); cursor: pointer; transition: all .2s; text-decoration: none; flex-shrink: 0; }
        .lang-btn:hover { border-color: var(--green); color: var(--green); }
        .lang-btn svg { width: 17px; height: 17px; }
        .nav-divider { width: 1px; height: 22px; background: var(--border); margin: 0 4px; }
        .hamburger { display: none; flex-direction: column; justify-content: center; gap: 5px; width: 40px; height: 40px; background: none; border: 1.5px solid var(--border); cursor: pointer; padding: 8px; border-radius: 8px; transition: border-color .2s; }
        .hamburger:hover { border-color: var(--green); }
        .hamburger span { display: block; height: 1.5px; width: 100%; background: var(--dark); border-radius: 2px; transition: all .3s; }
        .hamburger.open { border-color: var(--green); }
        .hamburger.open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }
        .mobile-menu { display: none; position: fixed; top: 68px; left: 0; right: 0; bottom: 0; background: var(--white); z-index: 99; padding: 20px 24px; flex-direction: column; gap: 0; overflow-y: auto; border-top: 1px solid var(--border); }
        .mobile-menu.open { display: flex; }
        .mobile-menu a { font-size: 15px; font-weight: 600; color: var(--dark); padding: 15px 0; border-bottom: 1px solid rgba(0,0,0,0.06); display: block; }
        .mobile-menu a:hover { color: var(--green); }
        .mobile-menu .mobile-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        .mobile-menu .mobile-actions .btn { width: 100%; justify-content: center; }

        /* ── FOOTER ── */
        .footer { background: var(--white); padding: 64px 0 32px; border-top: 1px solid var(--border); }
        .footer-top { display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 48px; }
        .footer-logo { font-size: 22px; font-weight: 800; color: var(--dark); letter-spacing: -0.5px; margin-bottom: 10px; }
        [dir="rtl"] .footer-logo { letter-spacing: 0; }
        .footer-tagline { font-size: 13px; color: #666; line-height: 1.65; max-width: 240px; margin-bottom: 20px; }
        .footer-socials { display: flex; gap: 12px; }
        .social-btn { width: 36px; height: 36px; border-radius: 8px; background: #f1f1f1; display: flex; align-items: center; justify-content: center; color: #444; transition: background .2s, color .2s; }
        .social-btn:hover { background: var(--green); color: var(--white); }
        .footer-col h5 { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #999; margin-bottom: 16px; }
        [dir="rtl"] .footer-col h5 { letter-spacing: 0; }
        .footer-col a { display: block; font-size: 13px; color: #555; margin-bottom: 10px; transition: color .2s; }
        .footer-col a:hover { color: var(--green); }
        .footer-bottom { border-top: 1px solid var(--border); padding-top: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .footer-copy { font-size: 12px; color: #999; }
        .footer-legal { display: flex; gap: 24px; flex-wrap: wrap; }
        .footer-legal a { font-size: 12px; color: #999; letter-spacing: 0.5px; text-transform: uppercase; transition: color .2s; }
        [dir="rtl"] .footer-legal a { letter-spacing: 0; }
        .footer-legal a:hover { color: var(--green); }

        /* ── SHARED RESPONSIVE ── */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .nav-actions .btn-ghost,
            .nav-actions .btn-nav-cta,
            .nav-actions .btn-demo,
            .nav-actions .lang-btn,
            .nav-divider { display: none; }
            .hamburger { display: flex; }
            .footer-top { grid-template-columns: 1fr 1fr; gap: 32px; }
        }
        @media (max-width: 480px) {
            .footer-top { grid-template-columns: 1fr; gap: 28px; }
            .footer-bottom { flex-direction: column; gap: 16px; text-align: center; }
            .footer-legal { justify-content: center; }
        }
    </style>
    @yield('styles')
</head>
<body>

    {{-- NAV --}}
    <nav class="nav">
        <div class="container nav-inner">
            <a href="/" class="nav-logo">
                QIMTA<span class="nav-logo-dot"></span>
            </a>
            <div class="nav-links">
                <a href="#">{{ __('welcome.nav.for_buyers') }}</a>
                <a href="#">{{ __('welcome.nav.for_brands') }}</a>
                <a href="#">{{ __('welcome.nav.catalog') }}</a>
                <a href="{{ route('news') }}">{{ __('welcome.nav.news') }}</a>
                <a href="#">{{ __('welcome.nav.how_it_works') }}</a>
                <a href="{{ route('about') }}" class="{{ Route::is('about') ? 'active' : '' }}">{{ __('welcome.nav.about') }}</a>
                {{-- More dropdown --}}
                <div class="nav-more" id="navMore">
                    <button class="nav-more-btn" id="navMoreBtn" aria-haspopup="true" aria-expanded="false">
                        {{ __('welcome.nav.more') }}
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-more-dropdown" id="navMoreDropdown" role="menu">
                        <a href="{{ route('contact') }}" class="{{ Route::is('contact') ? 'active' : '' }}" role="menuitem">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            {{ __('welcome.nav.contact') }}
                        </a>
                        <div class="nav-more-sep"></div>
                        <a href="{{ route('privacy') }}" class="{{ Route::is('privacy') ? 'active' : '' }}" role="menuitem">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            {{ __('welcome.nav.privacy') }}
                        </a>
                        <div class="nav-more-sep"></div>
                        <a href="{{ route('security') }}" class="{{ Route::is('security') ? 'active' : '' }}" role="menuitem">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            {{ __('welcome.nav.security') }}
                        </a>
                        <div class="nav-more-sep"></div>
                        <a href="{{ route('support') }}" class="{{ Route::is('support') ? 'active' : '' }}" role="menuitem">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            {{ __('welcome.nav.support') }}
                        </a>
                        <div class="nav-more-sep"></div>
                        <a href="{{ route('terms') }}" class="{{ Route::is('terms') ? 'active' : '' }}" role="menuitem">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            {{ __('welcome.nav.terms') }}
                        </a>
                        <div class="nav-more-sep"></div>
                        <a href="{{ route('cookie') }}" class="{{ Route::is('cookie') ? 'active' : '' }}" role="menuitem">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ __('welcome.nav.cookie') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="nav-actions">
                <a href="{{ route('locale.switch', $switchLocale) }}" class="lang-btn" title="{{ $switchLabel }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </a>
                <div class="nav-divider"></div>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-ghost">{{ __('welcome.nav.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost">{{ __('welcome.nav.login') }}</a>
                    @endauth
                @else
                    <a href="#" class="btn btn-ghost">{{ __('welcome.nav.login') }}</a>
                @endif
                @yield('nav-cta')
            </div>
            <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    {{-- Mobile Drawer --}}
    <div class="mobile-menu" id="mobileMenu">
        <a href="#">{{ __('welcome.nav.for_buyers') }}</a>
        <a href="#">{{ __('welcome.nav.for_brands') }}</a>
        <a href="#">{{ __('welcome.nav.catalog') }}</a>
        <a href="{{ route('news') }}">{{ __('welcome.nav.news') }}</a>
        <a href="#">{{ __('welcome.nav.how_it_works') }}</a>
        <a href="{{ route('about') }}">{{ __('welcome.nav.about') }}</a>
        <a href="{{ route('contact') }}">{{ __('welcome.nav.contact') }}</a>
        <a href="{{ route('privacy') }}">{{ __('welcome.nav.privacy') }}</a>
        <a href="{{ route('security') }}">{{ __('welcome.nav.security') }}</a>
        <a href="{{ route('support') }}">{{ __('welcome.nav.support') }}</a>
        <a href="{{ route('terms') }}">{{ __('welcome.nav.terms') }}</a>
        <a href="{{ route('cookie') }}">{{ __('welcome.nav.cookie') }}</a>
        <a href="{{ route('locale.switch', $switchLocale) }}">&#127760; {{ $switchLabel }}</a>
        <div class="mobile-actions">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline">{{ __('welcome.nav.dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">{{ __('welcome.nav.login') }}</a>
                @endauth
            @else
                <a href="#" class="btn btn-outline">{{ __('welcome.nav.login') }}</a>
            @endif
            @yield('mobile-cta')
        </div>
    </div>

    {{-- PAGE CONTENT --}}
    @yield('content')

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div>
                    <p class="footer-logo">QIMTA</p>
                    <p class="footer-tagline">{{ __('welcome.footer.tagline') }}</p>
                    <div class="footer-socials">
                        <a href="#" class="social-btn" title="LinkedIn"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                        <a href="#" class="social-btn" title="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L2.12 2.25h6.977l4.253 5.622 5.894-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                        <a href="#" class="social-btn" title="YouTube"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="white"/></svg></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h5>{{ __('welcome.footer.sol_h') }}</h5>
                    <a href="#">{{ __('welcome.footer.sol_1') }}</a>
                    <a href="#">{{ __('welcome.footer.sol_2') }}</a>
                    <a href="#">{{ __('welcome.footer.sol_3') }}</a>
                    <a href="#">{{ __('welcome.footer.sol_4') }}</a>
                </div>
                <div class="footer-col">
                    <h5>{{ __('welcome.footer.platform_h') }}</h5>
                    <a href="#">{{ __('welcome.footer.platform_1') }}</a>
                    <a href="#">{{ __('welcome.footer.platform_2') }}</a>
                    <a href="#">{{ __('welcome.footer.platform_3') }}</a>
                    <a href="#">{{ __('welcome.footer.platform_4') }}</a>
                </div>
                <div class="footer-col">
                    <h5>{{ __('welcome.footer.company_h') }}</h5>
                    <a href="#">{{ __('welcome.footer.company_1') }}</a>
                    <a href="#">{{ __('welcome.footer.company_2') }}</a>
                    <a href="#">{{ __('welcome.footer.company_3') }}</a>
                    <a href="#">{{ __('welcome.footer.company_4') }}</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copy">{{ __('welcome.footer.copy') }}</p>
                <div class="footer-legal">
                    <a href="#">{{ __('welcome.footer.compliance') }}</a>
                    <a href="#">{{ __('welcome.footer.docs') }}</a>
                    <a href="#">{{ __('welcome.footer.api_status') }}</a>
                    <a href="{{ route('privacy') }}">{{ __('welcome.footer.privacy') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        var hamburger  = document.getElementById('hamburger');
        var mobileMenu = document.getElementById('mobileMenu');
        hamburger.addEventListener('click', function () {
            var open = mobileMenu.classList.toggle('open');
            hamburger.classList.toggle('open', open);
            hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        });
        document.addEventListener('click', function (e) {
            if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.remove('open');
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
        // ── More dropdown ───────────────────────────────────────────────────
        var navMore    = document.getElementById('navMore');
        var navMoreBtn = document.getElementById('navMoreBtn');
        if (navMore && navMoreBtn) {
            navMoreBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = navMore.classList.toggle('open');
                navMoreBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            document.addEventListener('click', function (e) {
                if (!navMore.contains(e.target)) {
                    navMore.classList.remove('open');
                    navMoreBtn.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    navMore.classList.remove('open');
                    navMoreBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
