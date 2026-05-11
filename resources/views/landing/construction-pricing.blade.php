@extends('layouts.app')

@php 
    $isAr   = app()->getLocale() === 'ar'; 
    $__rp   = $isAr ? 'ar.' : '';
    $_p     = number_format($catalogStats['products']);
    $_cats  = $catalogStats['categories'];
    $_brands = $catalogStats['brands'];
@endphp

@section('title', $isAr
    ? 'تسعير مواد البناء في السعودية والخليج — كيمتا | ' . $_p . ' منتج'
    : 'Construction Materials Pricing in Saudi Arabia & GCC — Qimta | ' . $_p . ' Products')

@section('description', $isAr
    ? 'احصل على أسعار مواد البناء فوراً لأي مشروع في السعودية والخليج. كيمتا تفهرس ' . $_p . ' منتجاً من ' . $_brands . ' علامة تجارية مع تسعير تلقائي لجداول الكميات.'
    : 'Get instant construction materials pricing for any project in Saudi Arabia and GCC. Qimta indexes ' . $_p . ' products from ' . $_brands . ' brands with automatic BOQ pricing.')

@section('styles')
<style>
    .lp-hero { padding: 80px 0 72px; text-align: center; }
    .lp-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--green); margin-bottom: 16px; }
    .lp-hero h1 { font-size: clamp(32px, 5vw, 56px); font-weight: 900; letter-spacing: -1.5px; line-height: 1.1; margin-bottom: 20px; }
    .lp-hero p { font-size: 18px; color: #555; max-width: 640px; margin: 0 auto 36px; line-height: 1.7; }
    .lp-cta { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .lp-cta a { padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 15px; text-decoration: none; }
    .lp-cta .btn-primary { background: var(--green); color: #fff; }
    .lp-cta .btn-outline { border: 2px solid var(--border); color: #333; }
    .lp-stats { background: #f8faf8; padding: 56px 0; }
    .lp-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .lp-stat { text-align: center; }
    .lp-stat-val { font-size: clamp(28px, 4vw, 44px); font-weight: 900; color: var(--green); letter-spacing: -1px; }
    .lp-stat-label { font-size: 13px; color: #666; margin-top: 4px; }
    .lp-section { padding: 72px 0; }
    .lp-section h2 { font-size: clamp(24px, 3vw, 36px); font-weight: 800; letter-spacing: -0.5px; margin-bottom: 16px; }
    .lp-section p { font-size: 16px; color: #444; line-height: 1.8; margin-bottom: 16px; max-width: 800px; }
    .lp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start; }
    .lp-card { border: 1.5px solid var(--border); border-radius: 16px; padding: 28px; }
    .lp-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
    .lp-card p { font-size: 14px; color: #555; line-height: 1.7; margin: 0; }
    .lp-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 40px; }
    .lp-faq { padding: 72px 0; background: #f8faf8; }
    .lp-faq h2 { font-size: clamp(22px, 3vw, 32px); font-weight: 800; margin-bottom: 40px; text-align: center; }
    .lp-faq-item { border-bottom: 1px solid var(--border); padding: 20px 0; }
    .lp-faq-q { font-weight: 700; font-size: 16px; margin-bottom: 10px; }
    .lp-faq-a { font-size: 14px; color: #555; line-height: 1.8; }
    .lp-cta-section { padding: 80px 0; text-align: center; background: var(--green); color: #fff; }
    .lp-cta-section h2 { font-size: clamp(26px, 4vw, 42px); font-weight: 900; margin-bottom: 16px; color: #fff; }
    .lp-cta-section p { font-size: 16px; opacity: .85; margin-bottom: 32px; }
    .lp-cta-section a { background: #fff; color: var(--green); padding: 14px 32px; border-radius: 10px; font-weight: 800; text-decoration: none; font-size: 16px; }
    @media(max-width: 820px) {
        .lp-stats-grid, .lp-cards { grid-template-columns: repeat(2, 1fr); }
        .lp-grid { grid-template-columns: 1fr; gap: 32px; }
    }
    @media(max-width: 480px) {
        .lp-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .lp-cards { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

{{-- FAQ + BreadcrumbList Schema --}}
@php
$_lpSchema = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type'=>'ListItem','position'=>1,'name'=>'Qimta','item'=>'https://qimta.com/'],
                ['@type'=>'ListItem','position'=>2,'name'=>$isAr ? 'تسعير مواد البناء' : 'Construction Pricing','item'=>'https://qimta.com' . request()->getPathInfo()],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'كيف تسعّر كيمتا مواد البناء؟' : 'How does Qimta price construction materials?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'تستخدم كيمتا محرك RAG (Retrieval-Augmented Generation) لمطابقة كل بند في جدول الكميات مع ' . $_p . ' منتجاً معتمداً من قواعد بيانات المصنّعين، وتُرجع الأسعار في أقل من 60 ثانية بدقة 99.9%.'
                        : 'Qimta uses a RAG (Retrieval-Augmented Generation) engine to match every BOQ line item against ' . $_p . ' verified products from manufacturer databases, returning prices in under 60 seconds with 99.9% accuracy.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'هل التسعير مجاني؟' : 'Is construction pricing free?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'نعم، تسعير مواد البناء وجداول الكميات مجاني تماماً لمشتري مواد البناء والمقاولين وفرق المشتريات. تدفع العلامات التجارية فقط لإدراج منتجاتها.'
                        : 'Yes, construction materials pricing and BOQ pricing is completely free for buyers, contractors, and procurement teams. Brands pay only to list their products.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'ما المواد الإنشائية التي تغطيها كيمتا؟' : 'What construction materials does Qimta cover?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'تغطي كيمتا ' . $_p . ' منتجاً عبر ' . $_cats . ' قسماً إنشائياً تشمل: الهياكل الفولاذية، العزل، الزجاج والواجهات، الأنظمة الكهربائية، السباكة، الأرضيات، الإنهاء والتشطيب، وغيرها.'
                        : 'Qimta covers ' . $_p . ' products across ' . $_cats . ' construction divisions including: steel structures, insulation, glazing & facades, electrical systems, plumbing, flooring, finishing, and more.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'هل يعمل في السعودية والخليج؟' : 'Does it work in Saudi Arabia and GCC?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'نعم، كيمتا مبنية خصيصاً لمشاريع منطقة الخليج. الأسعار معايَرة وفق أسعار السوق السعودي وامتثال ضريبة القيمة المضافة وأوقات تسليم الموردين المحليين في الرياض وجدة ومناطق مشاريع نيوم.'
                        : 'Yes, Qimta is purpose-built for GCC construction projects. Pricing is calibrated to Saudi market rates, VAT compliance, and local supplier lead times across Riyadh, Jeddah, and NEOM project zones.'],
                ],
            ],
        ],
        [
            '@type' => 'WebPage',
            'name' => $isAr ? 'تسعير مواد البناء — كيمتا' : 'Construction Materials Pricing — Qimta',
            'description' => $isAr ? 'تسعير فوري لمواد البناء في السعودية والخليج' : 'Instant construction materials pricing in Saudi Arabia and GCC',
            'url' => 'https://qimta.com' . request()->getPathInfo(),
            'inLanguage' => $isAr ? 'ar' : 'en',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_lpSchema !!}</script>

{{-- HERO --}}
<section class="lp-hero">
    <div class="container">
        <p class="lp-eyebrow">{{ $isAr ? 'منصة التسعير الإنشائي' : 'Construction Pricing Platform' }}</p>
        <h1>{{ $isAr ? 'تسعير مواد البناء في ثوانٍ' : 'Construction Materials Pricing in Seconds' }}</h1>
        <p>{{ $isAr
            ? 'كيمتا تُسعّر كل بند في جدول كمياتك مقابل ' . $_p . ' منتجاً إنشائياً معتمداً في السعودية والخليج. دقة 99.9%، في أقل من 60 ثانية، مجاناً.'
            : 'Qimta prices every line item in your BOQ against ' . $_p . ' verified construction products across Saudi Arabia and GCC. 99.9% accuracy, under 60 seconds, free.' }}</p>
        <div class="lp-cta">
            <a href="{{ route('enduser.register') }}" class="btn-primary">{{ $isAr ? 'ابدأ مجاناً' : 'Start Free' }}</a>
            <a href="{{ route($__rp . 'catalog.index') }}" class="btn-outline">{{ $isAr ? 'تصفح الكتالوج' : 'Browse Catalog' }}</a>
        </div>
    </div>
</section>

{{-- STATS --}}
<section class="lp-stats">
    <div class="container">
        <div class="lp-stats-grid">
            <div class="lp-stat">
                <div class="lp-stat-val" itemprop="value" content="{{ $catalogStats['products'] }}">{{ $_p }}</div>
                <div class="lp-stat-label">{{ $isAr ? 'منتج إنشائي معتمد' : 'Verified Construction Products' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" itemprop="value" content="60">&lt;60s</div>
                <div class="lp-stat-label">{{ $isAr ? 'وقت التسعير لكل جدول كميات' : 'BOQ Pricing Time' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" itemprop="value" content="99.9">99.9%</div>
                <div class="lp-stat-label">{{ $isAr ? 'دقة محرك المطابقة' : 'Matching Engine Accuracy' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" itemprop="value" content="0">{{ $isAr ? 'مجاني' : 'FREE' }}</div>
                <div class="lp-stat-label">{{ $isAr ? 'للمشترين والمقاولين' : 'For Buyers & Contractors' }}</div>
            </div>
        </div>
    </div>
</section>

{{-- WHAT IS IT --}}
<section class="lp-section">
    <div class="container">
        <div class="lp-grid">
            <div>
                <h2>{{ $isAr ? 'ما هو تسعير مواد البناء الذكي؟' : 'What Is Smart Construction Materials Pricing?' }}</h2>
                <p>{{ $isAr
                    ? 'تسعير مواد البناء التقليدي يعتمد على عروض أسعار يدوية من موردين متعددين، وهو عملية تستغرق أياماً وتنتج أسعاراً قديمة وغير موثوقة.'
                    : 'Traditional construction materials pricing relies on manual quotes from multiple suppliers — a process that takes days and produces outdated, unreliable prices.' }}</p>
                <p>{{ $isAr
                    ? 'كيمتا تحل هذه المشكلة بمحرك RAG الذي يُطابق كل بند في جدول الكميات فورياً مع قواعد بيانات المصنّعين المباشرة، ويُرجع أسعاراً موثّقة وموحّدة لكل مشروع.'
                    : 'Qimta solves this with a RAG engine that instantly matches every BOQ line item against direct manufacturer databases, returning verified, standardized prices for every project.' }}</p>
                <p>{{ $isAr
                    ? 'النتيجة: توفير 80% من وقت دورة المشتريات، وتقليل الأخطاء البشرية، والحصول على أسعار السوق الفعلية لا التقديرات.'
                    : 'The result: 80% reduction in procurement cycle time, elimination of human error, and actual market prices — not estimates.' }}</p>
            </div>
            <div>
                <div class="lp-cards">
                    <div class="lp-card">
                        <h3>{{ $isAr ? '⚡ فوري' : '⚡ Instant' }}</h3>
                        <p>{{ $isAr ? 'نتائج في أقل من 60 ثانية لأي حجم جدول كميات' : 'Results in under 60 seconds for any BOQ size' }}</p>
                    </div>
                    <div class="lp-card">
                        <h3>{{ $isAr ? '✅ معتمد' : '✅ Verified' }}</h3>
                        <p>{{ $isAr ? 'أسعار من قواعد بيانات المصنّعين المباشرة' : 'Prices from direct manufacturer databases' }}</p>
                    </div>
                    <div class="lp-card">
                        <h3>{{ $isAr ? '🏗️ شامل' : '🏗️ Comprehensive' }}</h3>
                        <p>{{ $isAr ? $_p . ' منتج عبر ' . $_cats . ' قسم إنشائي' : $_p . ' products across ' . $_cats . ' construction divisions' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CATEGORIES --}}
<section class="lp-section" style="background:#f8faf8;padding:72px 0;">
    <div class="container">
        <h2 style="font-size:clamp(22px,3vw,32px);font-weight:800;margin-bottom:12px;text-align:center;">
            {{ $isAr ? 'فئات مواد البناء الرئيسية' : 'Main Construction Material Categories' }}
        </h2>
        <p style="text-align:center;color:#555;margin-bottom:40px;">
            {{ $isAr ? $_cats . ' قسماً إنشائياً — ' . $_brands . ' علامة تجارية معتمدة' : $_cats . ' construction divisions — ' . $_brands . ' verified brands' }}
        </p>
        <div class="lp-cards">
            @foreach([
                ['ar'=>'الهياكل الفولاذية','en'=>'Steel Structures','slug'=>'steel-structures'],
                ['ar'=>'العزل الحراري والصوتي','en'=>'Insulation','slug'=>'insulation'],
                ['ar'=>'الزجاج والواجهات','en'=>'Glazing & Facades','slug'=>'glazing'],
                ['ar'=>'الأنظمة الكهربائية','en'=>'Electrical Systems','slug'=>'electrical'],
                ['ar'=>'السباكة والصرف الصحي','en'=>'Plumbing & Drainage','slug'=>'plumbing'],
                ['ar'=>'الأرضيات والتشطيبات','en'=>'Flooring & Finishing','slug'=>'flooring'],
            ] as $cat)
            <div class="lp-card">
                <h3>{{ $isAr ? $cat['ar'] : $cat['en'] }}</h3>
                <p><a href="{{ route($__rp.'catalog.index') }}" style="color:var(--green);text-decoration:none;font-size:13px;">{{ $isAr ? 'تصفح المنتجات ←' : 'Browse Products →' }}</a></p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="lp-faq">
    <div class="container" style="max-width:800px;">
        <h2>{{ $isAr ? 'أسئلة شائعة عن تسعير مواد البناء' : 'FAQ — Construction Materials Pricing' }}</h2>
        @foreach([
            ['q'=> $isAr ? 'كيف تسعّر كيمتا مواد البناء؟' : 'How does Qimta price construction materials?',
             'a'=> $isAr ? 'تستخدم كيمتا محرك RAG لمطابقة كل بند مع ' . $_p . ' منتج معتمد من قواعد بيانات المصنّعين، وتُرجع الأسعار في أقل من 60 ثانية بدقة 99.9%.' : 'Qimta uses a RAG engine to match every BOQ line item against ' . $_p . ' verified products from manufacturer databases, returning prices in under 60 seconds with 99.9% accuracy.'],
            ['q'=> $isAr ? 'هل التسعير مجاني للمقاولين؟' : 'Is pricing free for contractors?',
             'a'=> $isAr ? 'نعم، التسعير مجاني تماماً لمشتري مواد البناء والمقاولين وفرق المشتريات.' : 'Yes, pricing is completely free for construction buyers, contractors, and procurement teams.'],
            ['q'=> $isAr ? 'هل يعمل في السعودية والخليج؟' : 'Does it work in Saudi Arabia and GCC?',
             'a'=> $isAr ? 'نعم، كيمتا مبنية خصيصاً للسوق السعودي ودول الخليج مع امتثال لضريبة القيمة المضافة وأسعار السوق المحلية.' : 'Yes, Qimta is purpose-built for Saudi Arabia and GCC with local market rates and VAT compliance.'],
            ['q'=> $isAr ? 'ما حجم الكتالوج؟' : 'How large is the catalog?',
             'a'=> $isAr ? $_p . ' منتجاً عبر ' . $_cats . ' قسماً إنشائياً و' . $_brands . ' علامة تجارية معتمدة.' : $_p . ' products across ' . $_cats . ' construction divisions and ' . $_brands . ' verified brands.'],
        ] as $faq)
        <div class="lp-faq-item">
            <div class="lp-faq-q">{{ $faq['q'] }}</div>
            <div class="lp-faq-a">{{ $faq['a'] }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section class="lp-cta-section">
    <div class="container">
        <h2>{{ $isAr ? 'ابدأ تسعير مشروعك الآن' : 'Start Pricing Your Project Now' }}</h2>
        <p>{{ $isAr ? 'مجاني، فوري، دقيق — لكل مشروع إنشائي في السعودية والخليج' : 'Free, instant, accurate — for every construction project in Saudi Arabia and GCC' }}</p>
        <a href="{{ route('enduser.register') }}">{{ $isAr ? 'ابدأ مجاناً' : 'Get Started Free' }}</a>
    </div>
</section>

@endsection
