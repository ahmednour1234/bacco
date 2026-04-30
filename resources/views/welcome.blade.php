<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>QIMTA â€” The Global Construction Pricing Platform</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            :root {
                --green: #1a5c3c;
                --green-btn: #1f6645;
                --green-light: #b8cfc3;
                --green-sage: #8fac9f;
                --dark: #111111;
                --cream: #f5f4f0;
                --white: #ffffff;
                --gray-text: #666666;
                --border: #e0e0e0;
                font-family: 'Inter', sans-serif;
            }
            html { scroll-behavior: smooth; }
            body { background: var(--white); color: var(--dark); font-size: 15px; line-height: 1.6; overflow-x: hidden; }
            a { text-decoration: none; color: inherit; }
            ul { list-style: none; }
            img { max-width: 100%; display: block; }
            .container { max-width: 1120px; margin: 0 auto; padding: 0 28px; }

            /* â”€â”€ NAV â”€â”€ */
            .nav { position: sticky; top: 0; z-index: 100; background: var(--white); border-bottom: 1px solid var(--border); }
            .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
            .nav-logo { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color: var(--green); }
            .nav-links { display: flex; align-items: center; gap: 28px; }
            .nav-links a { font-size: 14px; font-weight: 500; color: #333; transition: color .2s; }
            .nav-links a:hover { color: var(--green); }
            .nav-actions { display: flex; align-items: center; gap: 12px; }
            .btn { display: inline-flex; align-items: center; padding: 9px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; border: none; }
            .btn-ghost { background: transparent; color: #333; }
            .btn-ghost:hover { color: var(--green); }
            .btn-primary { background: var(--green-btn); color: var(--white); }
            .btn-primary:hover { background: #174f34; }
            .btn-dark { background: var(--dark); color: var(--white); }
            .btn-dark:hover { background: #333; }
            .btn-outline { background: transparent; color: var(--dark); border: 2px solid var(--dark); }
            .btn-outline:hover { background: var(--dark); color: var(--white); }
            .btn-outline-white { background: transparent; color: var(--white); border: 2px solid var(--white); }
            .btn-outline-white:hover { background: rgba(255,255,255,.1); }
            .btn-lg { padding: 14px 28px; font-size: 15px; border-radius: 8px; }

            /* â”€â”€ HERO â”€â”€ */
            .hero { padding: 90px 0 80px; background: var(--white); }
            .hero-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
            .hero-tag { font-size: 11px; font-weight: 700; letter-spacing: 2px; color: var(--green); text-transform: uppercase; margin-bottom: 20px; }
            .hero h1 { font-size: 56px; font-weight: 900; line-height: 1.05; letter-spacing: -2px; margin-bottom: 22px; color: var(--dark); }
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

            /* â”€â”€ STATS â”€â”€ */
            .stats { background: #c8dbd2; padding: 64px 0; }
            .stats-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; color: var(--green); text-transform: uppercase; margin-bottom: 6px; }
            .stats-sub { font-size: 13px; color: #555; margin-bottom: 36px; }
            .stats-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; }
            .stats-link { font-size: 13px; font-weight: 600; color: var(--green); display: flex; align-items: center; gap: 6px; }
            .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
            .stat-card { background: var(--white); border-radius: 12px; padding: 28px 24px; }
            .stat-icon { font-size: 20px; margin-bottom: 12px; color: #555; }
            .stat-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #888; margin-bottom: 10px; }
            .stat-value { font-size: 42px; font-weight: 900; color: var(--dark); letter-spacing: -2px; line-height: 1; }
            .stat-line { width: 36px; height: 3px; background: var(--green); margin-top: 14px; border-radius: 2px; }

            /* â”€â”€ PROBLEM â”€â”€ */
            .problem { padding: 80px 0; background: var(--white); }
            .section-intro { font-size: 15px; color: #333; max-width: 520px; margin-bottom: 48px; }
            .section-intro strong { display: block; font-size: 18px; font-weight: 700; margin-bottom: 6px; color: var(--dark); }
            .problem-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
            .problem-card { border: 1px solid var(--border); border-radius: 12px; padding: 28px 24px; }
            .problem-icon { font-size: 22px; color: #888; margin-bottom: 14px; }
            .problem-title { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
            .problem-desc { font-size: 14px; color: var(--gray-text); line-height: 1.65; }

            /* â”€â”€ HOW IT WORKS â”€â”€ */
            .how { padding: 80px 0; background: var(--cream); }
            .section-title { font-size: 22px; font-weight: 700; color: var(--dark); text-align: center; margin-bottom: 40px; }
            .how-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
            .how-card { background: var(--white); border-radius: 12px; padding: 28px 22px; border: 1px solid var(--border); }
            .how-card.active { background: var(--green-btn); color: var(--white); border-color: transparent; }
            .how-num { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: #aaa; margin-bottom: 14px; }
            .how-card.active .how-num { color: rgba(255,255,255,.7); }
            .how-title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
            .how-desc { font-size: 13px; color: var(--gray-text); line-height: 1.65; }
            .how-card.active .how-desc { color: rgba(255,255,255,.8); }
            .how-icon { font-size: 26px; margin-bottom: 14px; }

            /* â”€â”€ PILLARS â”€â”€ */
            .pillars { background: var(--dark); padding: 40px 0; }
            .pillars-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: #333; }
            .pillar { padding: 36px 32px; background: var(--dark); }
            .pillar-num { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; color: #666; text-transform: uppercase; margin-bottom: 8px; }
            .pillar-title { font-size: 15px; font-weight: 800; color: var(--white); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
            .pillar-desc { font-size: 13px; color: #888; }

            /* â”€â”€ ECOSYSTEM â”€â”€ */
            .eco { padding: 80px 0; background: var(--white); }
            .eco-label { font-size: 14px; font-weight: 600; color: #555; margin-bottom: 32px; }
            .eco-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
            .eco-card { border: 1px solid var(--border); border-radius: 12px; padding: 32px 28px; }
            .eco-title { font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
            .eco-desc { font-size: 14px; color: var(--gray-text); margin-bottom: 20px; line-height: 1.65; }
            .eco-link { font-size: 13px; font-weight: 600; color: var(--green); display: inline-flex; align-items: center; gap: 6px; }
            .eco-link:hover { text-decoration: underline; }

            /* â”€â”€ COMPARISON TABLE â”€â”€ */
            .compare { padding: 80px 0; background: var(--cream); }
            .compare table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 20px rgba(0,0,0,.06); }
            .compare th, .compare td { padding: 18px 24px; text-align: left; font-size: 14px; border-bottom: 1px solid var(--border); }
            .compare th { font-size: 13px; font-weight: 700; color: #888; background: #fafafa; }
            .compare th.col-qimta { background: var(--green-btn); color: var(--white); text-align: center; }
            .compare td.col-qimta { background: rgba(31,102,69,.06); text-align: center; font-weight: 700; color: var(--green); }
            .compare td.col-trad { color: #888; }
            .compare tr:last-child td, .compare tr:last-child th { border-bottom: none; }
            .check { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--green-btn); border-radius: 50%; color: var(--white); font-size: 13px; }

            /* â”€â”€ ENGINE â”€â”€ */
            .engine { padding: 80px 0; background: var(--white); }
            .engine-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
            .engine-card { background: var(--dark); border-radius: 16px; padding: 36px; color: var(--white); }
            .engine-title { font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #aaa; margin-bottom: 8px; }
            .engine-name { font-size: 22px; font-weight: 800; color: var(--white); margin-bottom: 14px; }
            .engine-desc { font-size: 14px; color: #aaa; line-height: 1.65; margin-bottom: 28px; }
            .engine-feat { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; }
            .engine-feat-icon { font-size: 18px; margin-top: 2px; color: var(--green-light); flex-shrink: 0; }
            .engine-feat-title { font-size: 14px; font-weight: 700; color: var(--white); }
            .engine-feat-sub { font-size: 13px; color: #888; }
            .engine-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .metric-card { border: 1px solid var(--border); border-radius: 12px; padding: 28px 24px; text-align: center; }
            .metric-val { font-size: 38px; font-weight: 900; color: var(--green); letter-spacing: -1px; margin-bottom: 6px; }
            .metric-label { font-size: 13px; color: #888; font-weight: 500; }

            /* â”€â”€ DIVISIONS â”€â”€ */
            .divs { padding: 60px 0; background: var(--cream); }
            .divs-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
            .div-card { background: var(--white); border: 1px solid var(--border); border-radius: 10px; padding: 18px 16px; }
            .div-num { font-size: 11px; font-weight: 700; color: #aaa; letter-spacing: 0.5px; margin-bottom: 4px; }
            .div-name { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
            .div-count { font-size: 12px; color: #888; }

            /* â”€â”€ NEWS â”€â”€ */
            .news { padding: 80px 0; background: var(--white); }
            .news-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
            .news-top-title { font-size: 16px; font-weight: 700; color: var(--dark); }
            .news-link { font-size: 13px; font-weight: 600; color: var(--green); display: flex; align-items: center; gap: 4px; }
            .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
            .news-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
            .news-img { height: 180px; object-fit: cover; width: 100%; background: #ccc; }
            .news-img-placeholder { height: 180px; display: flex; align-items: center; justify-content: center; font-size: 36px; }
            .news-body { padding: 20px; }
            .news-tag { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
            .news-tag.market { color: #e07b00; }
            .news-tag.tech { color: var(--green); }
            .news-tag.case { color: #5555cc; }
            .news-title { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
            .news-desc { font-size: 13px; color: var(--gray-text); line-height: 1.6; }

            /* â”€â”€ BRAND CTA â”€â”€ */
            .brand { padding: 0; }
            .brand-inner { display: grid; grid-template-columns: 1fr 1fr; }
            .brand-left { background: var(--dark); padding: 72px 56px; }
            .brand-left h3 { font-size: 26px; font-weight: 800; color: var(--white); margin-bottom: 12px; }
            .brand-left p { font-size: 14px; color: #aaa; margin-bottom: 32px; max-width: 380px; }
            .listing-row { display: flex; align-items: center; justify-content: space-between; background: #1e1e1e; border-radius: 8px; padding: 16px 20px; margin-bottom: 10px; font-size: 14px; color: var(--white); }
            .listing-badge { font-size: 12px; font-weight: 700; color: #aaa; }
            .listing-badge.free { color: var(--green-light); }
            .listing-badge.sales { color: #f0a800; }
            .brand-right { background: #1a1a1a; padding: 72px 56px; }
            .brand-right h4 { font-size: 14px; font-weight: 700; color: var(--white); margin-bottom: 20px; }
            .brand-benefit { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; font-size: 14px; color: #ccc; }
            .brand-benefit::before { content: "âœ“"; display: flex; align-items: center; justify-content: center; width: 20px; height: 20px; background: var(--green-btn); border-radius: 50%; color: var(--white); font-size: 11px; font-weight: 700; flex-shrink: 0; }

            /* â”€â”€ CTA BANNER â”€â”€ */
            .cta-banner { padding: 80px 0; background: var(--white); text-align: center; }
            .cta-banner h2 { font-size: 36px; font-weight: 900; color: var(--dark); margin-bottom: 12px; letter-spacing: -1px; }
            .cta-banner p { font-size: 15px; color: var(--gray-text); margin-bottom: 30px; }

            /* â”€â”€ FAQ â”€â”€ */
            .faq { padding: 80px 0; background: var(--cream); }
            .faq-inner { max-width: 680px; margin: 0 auto; }
            .faq-title { font-size: 22px; font-weight: 700; color: var(--dark); text-align: center; margin-bottom: 36px; }
            .faq-item { border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; background: var(--white); overflow: hidden; }
            .faq-q { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--dark); user-select: none; }
            .faq-q svg { flex-shrink: 0; transition: transform .25s; color: #888; }
            .faq-item.open .faq-q svg { transform: rotate(180deg); }
            .faq-a { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
            .faq-item.open .faq-a { max-height: 200px; }
            .faq-a-inner { padding: 0 22px 18px; font-size: 14px; color: var(--gray-text); line-height: 1.7; }

            /* â”€â”€ FOOTER â”€â”€ */
            .footer { background: var(--dark); padding: 64px 0 32px; }
            .footer-top { display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 48px; }
            .footer-logo { font-size: 22px; font-weight: 800; color: var(--white); letter-spacing: -0.5px; margin-bottom: 10px; }
            .footer-tagline { font-size: 13px; color: #888; line-height: 1.65; max-width: 220px; margin-bottom: 20px; }
            .footer-socials { display: flex; gap: 12px; }
            .social-btn { width: 34px; height: 34px; border-radius: 6px; background: #222; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #888; transition: background .2s; }
            .social-btn:hover { background: #333; color: var(--white); }
            .footer-col h5 { font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #555; margin-bottom: 16px; }
            .footer-col a { display: block; font-size: 13px; color: #888; margin-bottom: 10px; transition: color .2s; }
            .footer-col a:hover { color: var(--white); }
            .footer-bottom { border-top: 1px solid #222; padding-top: 28px; display: flex; align-items: center; justify-content: space-between; }
            .footer-copy { font-size: 12px; color: #555; }
            .footer-legal { display: flex; gap: 24px; }
            .footer-legal a { font-size: 12px; color: #555; letter-spacing: 0.5px; text-transform: uppercase; transition: color .2s; }
            .footer-legal a:hover { color: #aaa; }

            /* â”€â”€ RESPONSIVE â”€â”€ */
            @media (max-width: 1024px) {
                .hero h1 { font-size: 44px; }
                .stats-grid { grid-template-columns: repeat(2, 1fr); }
                .divs-grid { grid-template-columns: repeat(3, 1fr); }
                .footer-top { grid-template-columns: 1fr 1fr; }
            }
            @media (max-width: 768px) {
                .nav-links { display: none; }
                .hero-inner { grid-template-columns: 1fr; }
                .hero h1 { font-size: 38px; }
                .hero-mockup { display: none; }
                .problem-grid, .how-grid, .eco-grid, .news-grid { grid-template-columns: 1fr; }
                .pillars-grid { grid-template-columns: 1fr; }
                .engine-inner { grid-template-columns: 1fr; }
                .brand-inner { grid-template-columns: 1fr; }
                .compare { overflow-x: auto; }
                .stats-grid { grid-template-columns: 1fr 1fr; }
                .divs-grid { grid-template-columns: repeat(2, 1fr); }
                .footer-top { grid-template-columns: 1fr; gap: 28px; }
                .footer-bottom { flex-direction: column; gap: 16px; text-align: center; }
            }
        </style>
    </head>
    <body>

        <!-- â•â• NAV â•â• -->
        <nav class="nav">
            <div class="container nav-inner">
                <a href="/" class="nav-logo">QIMTA</a>
                <div class="nav-links">
                    <a href="#">For Buyers</a>
                    <a href="#">For Brands</a>
                    <a href="#">Catalog</a>
                    <a href="#">News</a>
                    <a href="#">How It Works</a>
                </div>
                <div class="nav-actions">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-ghost">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
                        @endauth
                    @else
                        <a href="#" class="btn btn-ghost">Login</a>
                    @endif
                    <a href="#" class="btn btn-primary">Price a BOQ &mdash; Free</a>
                </div>
            </div>
        </nav>

        <!-- â•â• HERO â•â• -->
        <section class="hero">
            <div class="container hero-inner">
                <div>
                    <p class="hero-tag">The Global Construction Pricing Platform</p>
                    <h1>Price every BOQ line, across every brand.</h1>
                    <p class="hero-sub">Access 418,326 products and 1B technical specs. Our RAG engine matches your requirements to manufacturer reality in seconds.</p>
                    <div class="hero-btns">
                        <a href="#" class="btn btn-dark btn-lg">Price a BOQ &mdash; Free</a>
                        <a href="#" class="btn btn-outline btn-lg">View Enterprise Solutions</a>
                    </div>
                </div>
                <div class="hero-mockup">
                    <div class="mock-header">
                        Construction Pricing <span class="mock-badge">Live</span>
                    </div>
                    <div class="mock-row"><span>Insulation</span><span>3,952</span><span>4,200</span></div>
                    <div class="mock-row"><span>Steel Frame</span><span>1,230</span><span>1,860</span></div>
                    <div class="mock-row"><span>Raised Access</span><span>3,900</span><span>1,035</span></div>
                    <div class="mock-actions">
                        <span class="mock-btn">Match BOQ</span>
                        <span class="mock-btn-alt">Export</span>
                    </div>
                    <div class="mock-row"><span>Tables</span><span>11,000</span><span>+47,820</span></div>
                    <div class="mock-row"><span>Outdoor Chairs</span><span>3,120</span><span>11,620</span></div>
                    <div class="mock-row"><span>Glazing</span><span>3,950</span><span>3,800</span></div>
                    <div class="mock-row"><span>Ground Branches</span><span>24,120</span><span>+</span></div>
                </div>
            </div>
        </section>

        <!-- â•â• STATS â•â• -->
        <section class="stats">
            <div class="container">
                <div class="stats-top">
                    <div>
                        <p class="stats-label">The Qimta Index</p>
                        <p class="stats-sub">Precision-engineered scale for global procurement.</p>
                    </div>
                    <a href="#" class="stats-link">Explore our Data Engine &rarr;</a>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">&#9783;</div>
                        <p class="stat-label">Products Tracked</p>
                        <div class="stat-value">418,326</div>
                        <div class="stat-line"></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#10010;</div>
                        <p class="stat-label">Technical Specs</p>
                        <div class="stat-value">1B+</div>
                        <div class="stat-line"></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#10004;</div>
                        <p class="stat-label">Brand Coverage</p>
                        <div class="stat-value">100%</div>
                        <div class="stat-line"></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#36;</div>
                        <p class="stat-label">BOQ Pricing Cost</p>
                        <div class="stat-value">FREE</div>
                        <div class="stat-line"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• PROBLEM â•â• -->
        <section class="problem">
            <div class="container">
                <div class="section-intro">
                    <strong>Construction pricing is scattered.</strong>
                    Traditional procurement is slow, manual, and prone to error.
                </div>
                <div class="problem-grid">
                    <div class="problem-card">
                        <div class="problem-icon">&#128269;</div>
                        <p class="problem-title">Chased one by one</p>
                        <p class="problem-desc">Waiting for individual sales reps to return calls while projects stall on the desk.</p>
                    </div>
                    <div class="problem-card">
                        <div class="problem-icon">&#128336;</div>
                        <p class="problem-title">Days apart</p>
                        <p class="problem-desc">Quotes arriving in different formats, timelines, and units of measurement. Incomparable data.</p>
                    </div>
                    <div class="problem-card">
                        <div class="problem-icon">&#128203;</div>
                        <p class="problem-title">No single source</p>
                        <p class="problem-desc">Pricing data living in PDFs, emails, and Excel sheets. No technical validation at scale.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• HOW IT WORKS â•â• -->
        <section class="how">
            <div class="container">
                <p class="section-title">How Qimta Accelerates Pricing</p>
                <div class="how-grid">
                    <div class="how-card">
                        <div class="how-icon">&#128196;</div>
                        <p class="how-num">01</p>
                        <p class="how-title">Upload BOQ</p>
                        <p class="how-desc">Drop your Excel or PDF. Our parser handles any structure.</p>
                    </div>
                    <div class="how-card active">
                        <div class="how-icon">&#9889;</div>
                        <p class="how-num">02</p>
                        <p class="how-title">Instant RAG Match</p>
                        <p class="how-desc">The engine scans 1B specs to find identical or superior technical alternatives.</p>
                    </div>
                    <div class="how-card">
                        <div class="how-icon">&#128269;</div>
                        <p class="how-num">03</p>
                        <p class="how-title">See Every Brand</p>
                        <p class="how-desc">Transparent pricing from across the global manufacturer landscape.</p>
                    </div>
                    <div class="how-card">
                        <div class="how-icon">&#128722;</div>
                        <p class="how-num">04</p>
                        <p class="how-title">Purchase</p>
                        <p class="how-desc">Finalize with the brand or use the data to negotiate elsewhere.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• PILLARS â•â• -->
        <section class="pillars">
            <div class="container">
                <div class="pillars-grid">
                    <div class="pillar">
                        <p class="pillar-num">Pillar 01</p>
                        <p class="pillar-title">Unified</p>
                        <p class="pillar-desc">One platform. All brands. Zero fragmentation.</p>
                    </div>
                    <div class="pillar">
                        <p class="pillar-num">Pillar 02</p>
                        <p class="pillar-title">Instant</p>
                        <p class="pillar-desc">Real-time matching. No waiting for callbacks.</p>
                    </div>
                    <div class="pillar">
                        <p class="pillar-num">Pillar 03</p>
                        <p class="pillar-title">Transparent</p>
                        <p class="pillar-desc">Direct manufacturer specs. No hidden markups.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• ECOSYSTEM â•â• -->
        <section class="eco">
            <div class="container">
                <p class="eco-label">Built for the Ecosystem</p>
                <div class="eco-grid">
                    <div class="eco-card">
                        <p class="eco-title">Contractors</p>
                        <p class="eco-desc">Winning more bids by pricing 10x faster with verified technical data.</p>
                        <a href="#" class="eco-link">Learn more &rarr;</a>
                    </div>
                    <div class="eco-card">
                        <p class="eco-title">Procurement</p>
                        <p class="eco-desc">Centralizing data across projects to leverage volume and ensure compliance.</p>
                        <a href="#" class="eco-link">Learn more &rarr;</a>
                    </div>
                    <div class="eco-card">
                        <p class="eco-title">Brands</p>
                        <p class="eco-desc">Getting your technical specs directly in front of the decision-makers.</p>
                        <a href="#" class="eco-link">Learn more &rarr;</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• COMPARISON TABLE â•â• -->
        <section class="compare">
            <div class="container">
                <table>
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th class="col-qimta">Qimta Engine</th>
                            <th>Traditional Way</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Speed to Quote</td>
                            <td class="col-qimta">&lt; 60 Seconds</td>
                            <td class="col-trad">3-7 Working Days</td>
                        </tr>
                        <tr>
                            <td>Technical Validation</td>
                            <td class="col-qimta"><span class="check">âœ“</span></td>
                            <td class="col-trad">Manual Expert Review</td>
                        </tr>
                        <tr>
                            <td>Brand Coverage</td>
                            <td class="col-qimta">Global Catalog</td>
                            <td class="col-trad">Local Distributors Only</td>
                        </tr>
                        <tr>
                            <td>Data Accuracy</td>
                            <td class="col-qimta">99.9% RAG Verified</td>
                            <td class="col-trad">Variable (Human Error)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- â•â• ENGINE ARCHITECTURE â•â• -->
        <section class="engine">
            <div class="container engine-inner">
                <div class="engine-card">
                    <p class="engine-title">Engine Architecture</p>
                    <p class="engine-name">The Qimta RAG Engine</p>
                    <p class="engine-desc">Retrieval-Augmented Generation specifically tuned for structural, electrical, and mechanical specifications.</p>
                    <div class="engine-feat">
                        <div class="engine-feat-icon">&#9783;</div>
                        <div>
                            <p class="engine-feat-title">1 Billion Specs</p>
                            <p class="engine-feat-sub">Every nut, bolt, and grade of steel indexed.</p>
                        </div>
                    </div>
                    <div class="engine-feat">
                        <div class="engine-feat-icon">&#9889;</div>
                        <div>
                            <p class="engine-feat-title">Sub-60s Matching</p>
                            <p class="engine-feat-sub">Global search across thousands of manufacturers.</p>
                        </div>
                    </div>
                </div>
                <div class="engine-metrics">
                    <div class="metric-card">
                        <div class="metric-val">418k</div>
                        <p class="metric-label">Products</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val">99%</div>
                        <p class="metric-label">Match Rate</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val">24/7</div>
                        <p class="metric-label">Uptime</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val">&lt; 1s</div>
                        <p class="metric-label">Latency</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• DIVISIONS â•â• -->
        <section class="divs">
            <div class="container">
                <div class="divs-grid">
                    <div class="div-card"><p class="div-num">Div 03</p><p class="div-name">Concrete</p><p class="div-count">12,402 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 04</p><p class="div-name">Masonry</p><p class="div-count">8,190 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 05</p><p class="div-name">Metals</p><p class="div-count">26,561 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 07</p><p class="div-name">Thermal &amp; Moisture</p><p class="div-count">15,003 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 08</p><p class="div-name">Openings</p><p class="div-count">53,291 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 09</p><p class="div-name">Finishes</p><p class="div-count">42,891 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 21</p><p class="div-name">Fire Suppression</p><p class="div-count">5,620 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 22</p><p class="div-name">Plumbing</p><p class="div-count">28,109 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 23</p><p class="div-name">HVAC</p><p class="div-count">31,005 Products</p></div>
                    <div class="div-card"><p class="div-num">Div 26</p><p class="div-name">Electrical</p><p class="div-count">55,420 Products</p></div>
                </div>
            </div>
        </section>

        <!-- â•â• INDUSTRY NEWS â•â• -->
        <section class="news">
            <div class="container">
                <div class="news-top">
                    <p class="news-top-title">Industry Intelligence</p>
                    <a href="#" class="news-link">View all news &#8599;</a>
                </div>
                <div class="news-grid">
                    <div class="news-card">
                        <div class="news-img-placeholder" style="background:#d0c8bc;">&#127959;</div>
                        <div class="news-body">
                            <p class="news-tag market">Market Report</p>
                            <p class="news-title">Steel pricing volatility in Q3</p>
                            <p class="news-desc">Analysis of supply chain shifts and how Qimta's engine is tracking real-time fluctuations.</p>
                        </div>
                    </div>
                    <div class="news-card">
                        <div class="news-img-placeholder" style="background:#1a2a1a;">&#128161;</div>
                        <div class="news-body">
                            <p class="news-tag tech">Tech Update</p>
                            <p class="news-title">RAG Engine V2.0 Launch</p>
                            <p class="news-desc">Introducing sub-60 second matching for complex mechanical sub-assemblies.</p>
                        </div>
                    </div>
                    <div class="news-card">
                        <div class="news-img-placeholder" style="background:#c8c8c8;">&#129309;</div>
                        <div class="news-body">
                            <p class="news-tag case">Case Study</p>
                            <p class="news-title">Efficiency at Scale</p>
                            <p class="news-desc">How a Tier-1 contractor reduced procurement overhead by 40% using Qimta.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• BRAND CTA â•â• -->
        <section class="brand">
            <div class="brand-inner">
                <div class="brand-left">
                    <h3>List your brand on Qimta.</h3>
                    <p>Ensure your technical specs are the first ones seen by buyers when they upload a BOQ.</p>
                    <div class="listing-row">
                        <span>Basic Listing</span>
                        <span class="listing-badge free">Free</span>
                    </div>
                    <div class="listing-row">
                        <span>Exclusive Brand Partner</span>
                        <span class="listing-badge sales">Contact Sales</span>
                    </div>
                </div>
                <div class="brand-right">
                    <h4>Manufacturer Benefits</h4>
                    <div class="brand-benefit">Direct-to-buyer technical specs</div>
                    <div class="brand-benefit">Real-time market demand data</div>
                    <div class="brand-benefit">RAG-verified product matching</div>
                    <div class="brand-benefit">Seamless procurement integration</div>
                </div>
            </div>
        </section>

        <!-- â•â• FINAL CTA â•â• -->
        <section class="cta-banner">
            <div class="container">
                <h2>Bring us one BOQ.</h2>
                <p>Experience the speed of RAG-powered construction pricing. No cost, no obligation.</p>
                <a href="#" class="btn btn-primary btn-lg">Price a BOQ &mdash; Free</a>
            </div>
        </section>

        <!-- â•â• FAQ â•â• -->
        <section class="faq">
            <div class="container">
                <div class="faq-inner">
                    <p class="faq-title">Frequently Asked Questions</p>
                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            How accurate is the RAG matching engine?
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="faq-a"><div class="faq-a-inner">Our RAG engine achieves 99.9% accuracy by cross-referencing 1B+ technical specs against manufacturer databases, with continuous real-time updates to maintain precision.</div></div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            Is my data secure?
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="faq-a"><div class="faq-a-inner">Yes. All BOQ data is encrypted in transit and at rest. We never share your project data with third parties, and you retain full ownership of your uploaded documents.</div></div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            What formats of BOQ do you support?
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="faq-a"><div class="faq-a-inner">We support Excel (.xlsx, .xls), PDF, CSV, and most common BOQ formats. Our parser is built to handle varied structures automatically.</div></div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            Is there a limit to how many products I can price?
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="faq-a"><div class="faq-a-inner">The free tier includes unlimited BOQ items per submission. Enterprise plans offer additional features like batch processing, API access, and dedicated support.</div></div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            Can I integrate Qimta with my ERP?
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="faq-a"><div class="faq-a-inner">Yes. Our REST API supports integration with major ERP systems. Contact our enterprise team for custom integration support and documentation.</div></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â• FOOTER â•â• -->
        <footer class="footer">
            <div class="container">
                <div class="footer-top">
                    <div>
                        <p class="footer-logo">QIMTA</p>
                        <p class="footer-tagline">Engineering-led pricing intelligence for the global construction ecosystem. We unify manufacturer reality with project requirements.</p>
                        <div class="footer-socials">
                            <a href="#" class="social-btn">in</a>
                            <a href="#" class="social-btn">ð•</a>
                            <a href="#" class="social-btn">&#9679;</a>
                            <a href="#" class="social-btn">&#9654;</a>
                        </div>
                    </div>
                    <div class="footer-col">
                        <h5>Solutions</h5>
                        <a href="#">For Contractors</a>
                        <a href="#">For Procurement Teams</a>
                        <a href="#">For Manufacturers</a>
                        <a href="#">Enterprise Integration</a>
                    </div>
                    <div class="footer-col">
                        <h5>Platform</h5>
                        <a href="#">RAG Engine V2.0</a>
                        <a href="#">Global Product Catalog</a>
                        <a href="#">Technical Specs Database</a>
                        <a href="#">Pricing Analytics</a>
                    </div>
                    <div class="footer-col">
                        <h5>Company</h5>
                        <a href="#">About Qimta</a>
                        <a href="#">Careers</a>
                        <a href="#">Newsroom</a>
                        <a href="#">Contact</a>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p class="footer-copy">&copy; 2024 QIMTA. All rights reserved.</p>
                    <div class="footer-legal">
                        <a href="#">Compliance</a>
                        <a href="#">Documentation</a>
                        <a href="#">API Status</a>
                        <a href="#">Privacy</a>
                    </div>
                </div>
            </div>
        </footer>

        <script>
            function toggleFaq(el) {
                var item = el.parentElement;
                var isOpen = item.classList.contains('open');
                document.querySelectorAll('.faq-item.open').forEach(function(i) { i.classList.remove('open'); });
                if (!isOpen) { item.classList.add('open'); }
            }
        </script>
    </body>
</html>
