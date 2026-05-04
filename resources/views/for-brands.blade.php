@extends('layouts.app')

@section('title', 'For Brands & Manufacturers — Qimta')

@section('styles')
<style>
    /* ── HERO ── */
    .brands-hero {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: center;
        padding: 80px 0 72px;
    }
    @media (max-width: 820px) { .brands-hero { grid-template-columns: 1fr; gap: 40px; padding: 52px 0 48px; } }

    .brands-hero-eyebrow {
        font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: var(--green); margin-bottom: 18px;
    }
    .brands-hero h1 {
        font-size: clamp(32px, 4.5vw, 52px); font-weight: 900; letter-spacing: -1.5px;
        line-height: 1.1; margin-bottom: 20px; color: var(--dark);
    }
    .brands-hero p {
        font-size: 15px; color: #555; line-height: 1.75; max-width: 460px; margin-bottom: 32px;
    }
    .hero-cta { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--green); color: #fff; padding: 13px 24px;
        border-radius: 10px; font-size: 14px; font-weight: 700;
        text-decoration: none; transition: background .2s, transform .15s;
        border: none; cursor: pointer;
    }
    .btn-primary:hover { background: #005a32; transform: translateY(-1px); }
    .btn-outline-dark {
        display: inline-flex; align-items: center; gap: 8px;
        border: 1.5px solid #ccc; color: #444; padding: 13px 24px;
        border-radius: 10px; font-size: 14px; font-weight: 600;
        text-decoration: none; transition: border-color .2s, color .2s;
    }
    .btn-outline-dark:hover { border-color: var(--green); color: var(--green); }

    .hero-img-wrap {
        border-radius: 20px; overflow: hidden; aspect-ratio: 4/3;
        background: linear-gradient(135deg, #1a3a2a 0%, #0d2419 100%);
        display: flex; align-items: center; justify-content: center;
        position: relative;
    }
    .hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; opacity: .85; }
    .hero-img-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(0,106,59,.4) 0%, transparent 60%);
    }

    /* ── STATS BAR ── */
    .stats-bar {
        display: grid; grid-template-columns: repeat(4, 1fr);
        border: 1.5px solid var(--border); border-radius: 16px;
        overflow: hidden; margin-bottom: 80px; background: var(--white);
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
    }
    @media (max-width: 700px) { .stats-bar { grid-template-columns: repeat(2, 1fr); } }
    .stat-cell {
        padding: 28px 24px; border-right: 1.5px solid var(--border);
        text-align: center;
    }
    .stat-cell:last-child { border-right: none; }
    .stat-cell .s-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 6px; }
    .stat-cell .s-val { font-size: 32px; font-weight: 900; letter-spacing: -1.5px; color: var(--green); line-height: 1; }
    .stat-cell .s-sub { font-size: 12px; color: #888; margin-top: 4px; }

    /* ── SECTION TITLE ── */
    .section-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 10px; text-align: center; }
    .section-title { font-size: clamp(24px, 3vw, 36px); font-weight: 900; letter-spacing: -0.8px; text-align: center; margin-bottom: 48px; }
    .section-title span { color: var(--green); }

    /* ── ADVANTAGES GRID ── */
    .advantages-section { margin-bottom: 88px; }
    .advantages-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    @media (max-width: 700px) { .advantages-grid { grid-template-columns: 1fr; } }
    .adv-card {
        border: 1.5px solid var(--border); border-radius: 16px; padding: 32px;
        background: var(--white); transition: border-color .2s, box-shadow .2s;
    }
    .adv-card:hover { border-color: var(--green); box-shadow: 0 4px 24px rgba(0,106,59,.08); }
    .adv-icon {
        width: 44px; height: 44px; border-radius: 12px; background: #f0fdf4;
        display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
    }
    .adv-icon svg { width: 22px; height: 22px; stroke: var(--green); fill: none; stroke-width: 1.8; }
    .adv-card h4 { font-size: 17px; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.3px; }
    .adv-card p { font-size: 13.5px; color: #666; line-height: 1.7; }

    /* ── HOW IT WORKS ── */
    .how-section {
        background: #0d1f17; border-radius: 24px; padding: 64px;
        margin-bottom: 88px; color: #fff;
    }
    @media (max-width: 700px) { .how-section { padding: 40px 28px; } }
    .how-section .section-eyebrow { color: #4ade80; }
    .how-section .section-title { color: #fff; }
    .how-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; margin-top: 48px; }
    @media (max-width: 820px) { .how-steps { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .how-steps { grid-template-columns: 1fr; } }
    .how-step { position: relative; }
    .step-num {
        width: 36px; height: 36px; border-radius: 10px; background: var(--green);
        color: #fff; font-size: 14px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    }
    .how-step h5 { font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #fff; }
    .how-step p { font-size: 13px; color: rgba(255,255,255,.6); line-height: 1.65; }

    /* ── PRICING ── */
    .pricing-section { margin-bottom: 88px; }
    .pricing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 700px) { .pricing-grid { grid-template-columns: 1fr; } }
    .pricing-card {
        border: 1.5px solid var(--border); border-radius: 20px; padding: 36px;
        background: var(--white); position: relative; transition: box-shadow .2s;
    }
    .pricing-card.featured {
        border-color: var(--green); border-width: 2px;
        box-shadow: 0 8px 40px rgba(0,106,59,.12);
    }
    .pricing-badge {
        position: absolute; top: -13px; right: 24px;
        background: var(--green); color: #fff;
        font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
        padding: 4px 14px; border-radius: 20px;
    }
    .pricing-tier { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #aaa; margin-bottom: 8px; }
    .pricing-card.featured .pricing-tier { color: var(--green); }
    .pricing-name { font-size: 26px; font-weight: 900; letter-spacing: -0.8px; margin-bottom: 12px; }
    .pricing-desc { font-size: 13px; color: #666; line-height: 1.65; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
    .pricing-features { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }
    .pricing-features li { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #444; }
    .pricing-features li svg { width: 16px; height: 16px; stroke: var(--green); fill: none; stroke-width: 2.5; flex-shrink: 0; }
    .pricing-btn {
        display: block; text-align: center; padding: 12px;
        border-radius: 10px; font-size: 13px; font-weight: 700;
        text-decoration: none; transition: all .2s; letter-spacing: 0.5px; text-transform: uppercase;
    }
    .pricing-btn.outline { border: 1.5px solid var(--green); color: var(--green); }
    .pricing-btn.outline:hover { background: var(--green); color: #fff; }
    .pricing-btn.solid { background: var(--green); color: #fff; }
    .pricing-btn.solid:hover { background: #005a32; }

    /* ── FORM SECTION ── */
    .form-section {
        display: grid; grid-template-columns: 1fr 1fr; gap: 64px;
        align-items: start; margin-bottom: 88px;
    }
    @media (max-width: 820px) { .form-section { grid-template-columns: 1fr; gap: 40px; } }
    .form-section-left h2 { font-size: clamp(22px, 2.8vw, 32px); font-weight: 900; letter-spacing: -0.6px; margin-bottom: 14px; }
    .form-section-left p { font-size: 14px; color: #555; line-height: 1.75; margin-bottom: 28px; }
    .form-bullets { display: flex; flex-direction: column; gap: 14px; }
    .form-bullet { display: flex; align-items: flex-start; gap: 12px; }
    .form-bullet-icon { width: 36px; height: 36px; border-radius: 10px; background: #f0fdf4; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
    .form-bullet-icon svg { width: 18px; height: 18px; stroke: var(--green); fill: none; stroke-width: 1.8; }
    .form-bullet h5 { font-size: 14px; font-weight: 700; margin-bottom: 3px; }
    .form-bullet p { font-size: 13px; color: #666; line-height: 1.55; }

    .brand-form { background: #f9fafb; border: 1.5px solid var(--border); border-radius: 20px; padding: 36px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 500px) { .form-row { grid-template-columns: 1fr; } }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 12px; font-weight: 700; color: #555; letter-spacing: 0.3px; }
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: 14px; font-family: inherit; color: var(--dark); background: #fff;
        outline: none; transition: border-color .2s;
        width: 100%;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: var(--green); }
    .form-group textarea { resize: vertical; min-height: 90px; }
    .form-submit {
        width: 100%; padding: 13px; background: var(--green); color: #fff;
        border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
        font-family: inherit; cursor: pointer; letter-spacing: 0.5px; text-transform: uppercase;
        transition: background .2s; margin-top: 4px;
    }
    .form-submit:hover { background: #005a32; }
</style>
@endsection

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────── --}}
<div class="container">
    <div class="brands-hero">
        <div>
            <div class="brands-hero-eyebrow">For Brands &amp; Manufacturers</div>
            <h1>Get your products priced, purchased, and delivered — globally.</h1>
            <p>Qimta acts as your global merchant partner, managing the complexity of international procurement while preserving your brand identity and technical integrity.</p>
            <div class="hero-cta">
                <a href="#apply" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Join Qimta as a Brand
                </a>
                <a href="{{ route('catalog.index') }}" class="btn-outline-dark">Browse Catalog</a>
            </div>
        </div>
        <div class="hero-img-wrap">
            <img src="https://images.unsplash.com/photo-1565043589221-1a6fd9ae45c7?w=800&q=80" alt="Manufacturing facility" onerror="this.style.display='none'">
            <div class="hero-img-overlay"></div>
        </div>
    </div>
</div>

{{-- ── STATS BAR ───────────────────────────────────────────── --}}
<div class="container">
    <div class="stats-bar">
        @php
            try {
                $divisions  = \Illuminate\Support\Facades\DB::connection('catalog')->table('catalog_products')->distinct()->count('division');
                $categories = \Illuminate\Support\Facades\DB::connection('catalog')->table('catalog_products')->distinct()->count('category_id');
                $products   = \Illuminate\Support\Facades\DB::connection('catalog')->table('catalog_products')->count();
            } catch(\Exception $e) {
                $divisions = 15; $categories = 206; $products = 418326;
            }
        @endphp
        <div class="stat-cell">
            <div class="s-label">Divisions</div>
            <div class="s-val">{{ $divisions }}</div>
            <div class="s-sub">Engineering verticals</div>
        </div>
        <div class="stat-cell">
            <div class="s-label">Categories</div>
            <div class="s-val">{{ number_format($categories) }}</div>
            <div class="s-sub">Indexed product types</div>
        </div>
        <div class="stat-cell">
            <div class="s-label">Products</div>
            <div class="s-val">{{ number_format($products) }}</div>
            <div class="s-sub">Live in catalog</div>
        </div>
        <div class="stat-cell">
            <div class="s-label">Specifications</div>
            <div class="s-val">~1B</div>
            <div class="s-sub">BOQ line items served</div>
        </div>
    </div>
</div>

{{-- ── THE QIMTA ADVANTAGE ──────────────────────────────────── --}}
<div class="container">
    <div class="advantages-section">
        <div class="section-eyebrow">Why Qimta</div>
        <div class="section-title">The Qimta <span>Advantage</span></div>
        <div class="advantages-grid">
            <div class="adv-card">
                <div class="adv-icon">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h4>Aggregated Buyer Demand</h4>
                <p>Access a global stream of verified buyer intent. We consolidate fragmented demand from massive construction projects, streamlining your sales funnel into high-volume purchase orders.</p>
            </div>
            <div class="adv-card">
                <div class="adv-icon">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h4>Identity Preserved</h4>
                <p>We don't white-label. Your brand stays front and center. Customers buy your engineering excellence, powered by our infrastructure.</p>
            </div>
            <div class="adv-card">
                <div class="adv-icon">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                </div>
                <h4>Product Visibility</h4>
                <p>Real-time indexing into Bill of Quantities (BOQs) globally. Ensure your specs are the standard for every new project that comes through our platform.</p>
            </div>
            <div class="adv-card">
                <div class="adv-icon">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <h4>Transparent Purchase Path</h4>
                <p>Full traceability from initial query to final payment. No hidden intermediaries — just a direct, efficient path from your warehouse to the global construction site.</p>
            </div>
        </div>
    </div>
</div>

{{-- ── HOW IT WORKS ─────────────────────────────────────────── --}}
<div class="container">
    <div class="how-section">
        <div class="section-eyebrow">Engineered Fulfillment</div>
        <div class="section-title">How Qimta integrates your<br>products into the global workflow.</div>
        <div class="how-steps">
            <div class="how-step">
                <div class="step-num">1</div>
                <h5>Data Submission</h5>
                <p>Submit technical catalogs, price lists, and compliance certifications via our secure API or portal.</p>
            </div>
            <div class="how-step">
                <div class="step-num">2</div>
                <h5>System Indexing</h5>
                <p>Our engines structure your data into a searchable, relational format ready for complex procurement queries.</p>
            </div>
            <div class="how-step">
                <div class="step-num">3</div>
                <h5>RAG Retrieval</h5>
                <p>Retrieval-Augmented Generation pairs buyer needs with your specific technical parameters for precise matching.</p>
            </div>
            <div class="how-step">
                <div class="step-num">4</div>
                <h5>Global Fulfillment</h5>
                <p>Qimta manages the transaction, logistics, and delivery, ensuring your product reaches the end buyer securely.</p>
            </div>
        </div>
    </div>
</div>

{{-- ── MARKET POSITIONING ───────────────────────────────────── --}}
<div class="container">
    <div class="pricing-section">
        <div class="section-eyebrow">Market Positioning</div>
        <div class="section-title">Choose your <span>partnership tier</span></div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-tier">Standard</div>
                <div class="pricing-name">Listing Package</div>
                <div class="pricing-desc">Unified listing across all Qimta marketplace verticals and global search results.</div>
                <ul class="pricing-features">
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Global Catalog Visibility</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Standard API Integration</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Data Refresh: Monthly</li>
                </ul>
                <a href="#apply" class="pricing-btn outline">Select Listing</a>
            </div>
            <div class="pricing-card featured">
                <div class="pricing-badge">Recommended</div>
                <div class="pricing-tier">Premium</div>
                <div class="pricing-name">Exclusivity Package</div>
                <div class="pricing-desc">Per-product category exclusivity, ensuring your brand is the primary recommendation for specific specs.</div>
                <ul class="pricing-features">
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Category Dominance</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Priority RAG Retrieval</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Dedicated Account Engineering</li>
                    <li><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Real-time Inventory Sync</li>
                </ul>
                <a href="#apply" class="pricing-btn solid">Select Exclusivity</a>
            </div>
        </div>
    </div>
</div>

{{-- ── APPLICATION FORM ─────────────────────────────────────── --}}
<div class="container" id="apply">
    <div class="form-section">
        <div class="form-section-left">
            <h2>Start your onboarding</h2>
            <p>Our partnership engineering team will review your data and category fit within 48 hours.</p>
            <div class="form-bullets">
                <div class="form-bullet">
                    <div class="form-bullet-icon">
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div>
                        <h5>Manufacturer Focus</h5>
                        <p>Direct integration with production cycles to optimize lead times.</p>
                    </div>
                </div>
                <div class="form-bullet">
                    <div class="form-bullet-icon">
                        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </div>
                    <div>
                        <h5>Global Logistics</h5>
                        <p>We handle cross-border duties, taxes, and shipping compliance.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="brand-form">
            <form action="#" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company" placeholder="Enterprise Ltd" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contact" placeholder="John Doe" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Work Email</label>
                    <input type="email" name="email" placeholder="john@company.com" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Category</label>
                        <select name="category">
                            <option>Structural Steel</option>
                            <option>Fire Fighting</option>
                            <option>Electrical / ELV</option>
                            <option>Mechanical / HVAC</option>
                            <option>Plumbing</option>
                            <option>Civil / Architecture</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Region</label>
                        <select name="region">
                            <option>North America</option>
                            <option>Europe</option>
                            <option>Middle East</option>
                            <option>Asia Pacific</option>
                            <option>Africa</option>
                            <option>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" placeholder="Tell us about your product range..."></textarea>
                </div>
                <button type="submit" class="form-submit">Submit Application</button>
            </form>
        </div>
    </div>
</div>

@endsection
