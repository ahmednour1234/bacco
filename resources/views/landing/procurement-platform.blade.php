@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; $__rp = $isAr ? 'ar.' : ''; @endphp

@section('title', $isAr
    ? 'منصة مشتريات الإنشاء B2B — كيمتا | السعودية والخليج'
    : 'B2B Construction Procurement Platform — Qimta | Saudi Arabia & GCC')

@section('description', $isAr
    ? 'كيمتا: منصة مشتريات الإنشاء B2B للسعودية والخليج. تربط المقاولين وفرق المشتريات بـ 418,326 منتج إنشائي من 72 علامة تجارية معتمدة — بتسعير فوري وموثّق.'
    : 'Qimta: B2B construction procurement platform for Saudi Arabia and GCC. Connects contractors and procurement teams to 418,326 construction products from 72 verified brands — with instant, verified pricing.')

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
    .lp-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 40px; }
    .lp-card { border: 1.5px solid var(--border); border-radius: 16px; padding: 28px; }
    .lp-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
    .lp-card p { font-size: 14px; color: #555; line-height: 1.7; margin: 0; }
    .lp-table { width: 100%; border-collapse: collapse; margin-top: 40px; font-size: 15px; }
    .lp-table th { text-align: start; padding: 12px 16px; border-bottom: 2px solid var(--border); font-size: 12px; letter-spacing: 1px; text-transform: uppercase; color: #888; }
    .lp-table td { padding: 14px 16px; border-bottom: 1px solid var(--border); }
    .lp-table tr:last-child td { border: none; }
    .check { color: var(--green); font-weight: 700; }
    .cross { color: #ccc; }
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
    }
    @media(max-width: 480px) {
        .lp-cards { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

@php
$_lpSchema = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type'=>'ListItem','position'=>1,'name'=>'Qimta','item'=>'https://qimta.com/'],
                ['@type'=>'ListItem','position'=>2,'name'=>$isAr ? 'منصة مشتريات الإنشاء' : 'Construction Procurement Platform','item'=>'https://qimta.com' . request()->getPathInfo()],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'ما هي منصة مشتريات الإنشاء؟' : 'What is a construction procurement platform?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'منصة مشتريات الإنشاء هي نظام رقمي يربط المقاولين وفرق المشتريات بموردي مواد البناء المعتمدين، ويُسهّل عمليات التسعير وإصدار طلبات الشراء ومقارنة الأسعار. كيمتا تقدم هذه الخدمة مجاناً مع فهرسة 418,326 منتجاً إنشائياً.'
                        : 'A construction procurement platform is a digital system connecting contractors and procurement teams to verified building materials suppliers, streamlining pricing, purchase requests, and price comparison. Qimta provides this service free with 418,326 indexed construction products.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'كيف تُميّز كيمتا نفسها عن منصات المشتريات الأخرى؟' : 'How does Qimta differentiate from other procurement platforms?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'كيمتا الوحيدة التي تُسعّر كل بند في جدول الكميات بشكل تلقائي من قواعد بيانات المصنّعين المباشرة — لا تقديرات، لا وسطاء. 418,326 منتج، دقة 99.9%، في أقل من 60 ثانية.'
                        : 'Qimta is the only platform that auto-prices every BOQ line item from direct manufacturer databases — no estimates, no middlemen. 418,326 products, 99.9% accuracy, in under 60 seconds.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'هل تخدم كيمتا مشاريع Vision 2030 في السعودية؟' : 'Does Qimta serve Vision 2030 projects in Saudi Arabia?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'نعم، كيمتا مبنية للسوق السعودي بما يشمل مشاريع نيوم، القدية، البحر الأحمر، وغيرها من مشاريع Vision 2030. الأسعار معايَرة وفق أسعار السوق السعودي وامتثال ضريبة القيمة المضافة.'
                        : 'Yes, Qimta is built for the Saudi market including NEOM, Qiddiya, Red Sea, and other Vision 2030 projects. Pricing is calibrated to Saudi market rates and VAT compliance.'],
                ],
            ],
        ],
        [
            '@type' => 'SoftwareApplication',
            'name' => 'Qimta',
            'applicationCategory' => 'BusinessApplication',
            'description' => $isAr ? 'منصة مشتريات الإنشاء B2B للسعودية والخليج' : 'B2B construction procurement platform for Saudi Arabia and GCC',
            'offers' => ['@type'=>'Offer','price'=>'0','priceCurrency'=>'SAR'],
            'operatingSystem' => 'Web',
            'url' => 'https://qimta.com',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_lpSchema !!}</script>

{{-- HERO --}}
<section class="lp-hero">
    <div class="container">
        <p class="lp-eyebrow">{{ $isAr ? 'منصة المشتريات الإنشائية B2B' : 'B2B Construction Procurement' }}</p>
        <h1>{{ $isAr ? 'منصة مشتريات الإنشاء للسعودية والخليج' : 'Construction Procurement Platform for Saudi Arabia & GCC' }}</h1>
        <p>{{ $isAr
            ? 'كيمتا تربط المقاولين وفرق المشتريات بـ 418,326 منتج إنشائي معتمد من 72 علامة تجارية. تسعير فوري، بيانات موثّقة، مجاناً.'
            : 'Qimta connects contractors and procurement teams to 418,326 verified construction products from 72 brands. Instant pricing, verified data, free.' }}</p>
        <div class="lp-cta">
            <a href="{{ route('enduser.register') }}" class="btn-primary">{{ $isAr ? 'ابدأ مجاناً' : 'Start Free' }}</a>
            <a href="{{ route($__rp . 'for-brands') }}" class="btn-outline">{{ $isAr ? 'للعلامات التجارية' : 'For Brands' }}</a>
        </div>
    </div>
</section>

{{-- STATS --}}
<section class="lp-stats">
    <div class="container">
        <div class="lp-stats-grid">
            <div class="lp-stat">
                <div class="lp-stat-val" content="418326">418,326</div>
                <div class="lp-stat-label">{{ $isAr ? 'منتج إنشائي في الفهرس' : 'Construction products indexed' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" content="72">72</div>
                <div class="lp-stat-label">{{ $isAr ? 'علامة تجارية معتمدة' : 'Verified brands' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" content="206">206</div>
                <div class="lp-stat-label">{{ $isAr ? 'قسم إنشائي مغطى' : 'Construction divisions covered' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" content="0">{{ $isAr ? 'مجاني' : 'FREE' }}</div>
                <div class="lp-stat-label">{{ $isAr ? 'للمشترين والمقاولين' : 'For buyers & contractors' }}</div>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section class="lp-section">
    <div class="container">
        <h2>{{ $isAr ? 'لماذا كيمتا لمشتريات الإنشاء؟' : 'Why Qimta for Construction Procurement?' }}</h2>
        <p>{{ $isAr
            ? 'مشتريات الإنشاء في السعودية والخليج معقّدة: موردون متعددون، أسعار متغيرة، ضريبة قيمة مضافة، ومتطلبات خاصة بكل مشروع. كيمتا تُبسّط هذه العملية بمحرك RAG الذي يُسعّر تلقائياً ويُوحّد البيانات من المصنّعين المباشرين.'
            : 'Construction procurement in Saudi Arabia and GCC is complex: multiple suppliers, volatile prices, VAT, and project-specific requirements. Qimta simplifies this with a RAG engine that auto-prices and unifies data from direct manufacturers.' }}
        </p>
        <div class="lp-cards">
            <div class="lp-card">
                <h3>{{ $isAr ? '🔍 تسعير فوري' : '🔍 Instant Pricing' }}</h3>
                <p>{{ $isAr ? 'أسعار موثّقة لأي جدول كميات في أقل من 60 ثانية من قواعد البيانات المباشرة للمصنّعين.' : 'Verified prices for any BOQ in under 60 seconds from direct manufacturer databases.' }}</p>
            </div>
            <div class="lp-card">
                <h3>{{ $isAr ? '📦 فهرس موحّد' : '📦 Unified Catalog' }}</h3>
                <p>{{ $isAr ? '418,326 منتج إنشائي عبر 206 قسماً و72 علامة تجارية في مكان واحد.' : '418,326 construction products across 206 divisions and 72 brands in one place.' }}</p>
            </div>
            <div class="lp-card">
                <h3>{{ $isAr ? '🏗️ مبني للخليج' : '🏗️ Built for GCC' }}</h3>
                <p>{{ $isAr ? 'أسعار معايَرة وفق السوق السعودي وامتثال ضريبة القيمة المضافة ومتطلبات مشاريع Vision 2030.' : 'Pricing calibrated to Saudi market rates, VAT compliance, and Vision 2030 project requirements.' }}</p>
            </div>
            <div class="lp-card">
                <h3>{{ $isAr ? '🔗 تكامل API' : '🔗 API Integration' }}</h3>
                <p>{{ $isAr ? 'REST API للتكامل مع أنظمة ERP وإدارة المشاريع الخاصة بك.' : 'REST API for integration with your ERP and project management systems.' }}</p>
            </div>
            <div class="lp-card">
                <h3>{{ $isAr ? '🔒 بيانات آمنة' : '🔒 Secure Data' }}</h3>
                <p>{{ $isAr ? 'بيانات المشاريع مشفّرة تماماً. لا مشاركة مع أطراف ثالثة. ملكيتك الكاملة.' : 'Project data fully encrypted. No sharing with third parties. Your full ownership.' }}</p>
            </div>
            <div class="lp-card">
                <h3>{{ $isAr ? '💸 مجاني للمشترين' : '💸 Free for Buyers' }}</h3>
                <p>{{ $isAr ? 'تسعير وجداول كميات غير محدودة مجاناً. الإيرادات من العلامات التجارية المُدرجة.' : 'Unlimited pricing and BOQs free. Revenue from listed brands.' }}</p>
            </div>
        </div>
    </div>
</section>

{{-- COMPARISON --}}
<section class="lp-section" style="background:#f8faf8;padding:72px 0;">
    <div class="container">
        <h2>{{ $isAr ? 'كيمتا مقارنةً بالطرق التقليدية' : 'Qimta vs Traditional Methods' }}</h2>
        <table class="lp-table">
            <thead>
                <tr>
                    <th>{{ $isAr ? 'الميزة' : 'Feature' }}</th>
                    <th>{{ $isAr ? 'كيمتا' : 'Qimta' }}</th>
                    <th>{{ $isAr ? 'التسعير اليدوي' : 'Manual Pricing' }}</th>
                    <th>{{ $isAr ? 'منصات أخرى' : 'Other Platforms' }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $isAr ? 'وقت التسعير' : 'Pricing time' }}</td>
                    <td class="check">&lt;60{{ $isAr ? ' ثانية' : 's' }}</td>
                    <td class="cross">{{ $isAr ? '3-10 أيام' : '3-10 days' }}</td>
                    <td class="cross">{{ $isAr ? 'ساعات' : 'Hours' }}</td>
                </tr>
                <tr>
                    <td>{{ $isAr ? 'حجم الفهرس' : 'Catalog size' }}</td>
                    <td class="check">418,326</td>
                    <td class="cross">{{ $isAr ? 'محدود' : 'Limited' }}</td>
                    <td class="cross">{{ $isAr ? 'متفاوت' : 'Varies' }}</td>
                </tr>
                <tr>
                    <td>{{ $isAr ? 'دقة البيانات' : 'Data accuracy' }}</td>
                    <td class="check">99.9%</td>
                    <td class="cross">{{ $isAr ? 'غير موثّقة' : 'Unverified' }}</td>
                    <td class="cross">{{ $isAr ? 'غير معروفة' : 'Unknown' }}</td>
                </tr>
                <tr>
                    <td>{{ $isAr ? 'التكلفة للمشتري' : 'Cost to buyer' }}</td>
                    <td class="check">{{ $isAr ? 'مجاني' : 'Free' }}</td>
                    <td>{{ $isAr ? 'وقت + جهد' : 'Time + effort' }}</td>
                    <td class="cross">{{ $isAr ? 'اشتراك مدفوع' : 'Paid subscription' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

{{-- LONG-FORM --}}
<section class="lp-section">
    <div class="container" style="max-width:860px;">
        <h2>{{ $isAr ? 'دليل مشتريات الإنشاء في السعودية' : 'Construction Procurement Guide for Saudi Arabia' }}</h2>
        <p>{{ $isAr
            ? 'مشتريات الإنشاء في المملكة العربية السعودية تعرف نمواً استثنائياً مع مشاريع Vision 2030 التي تتجاوز قيمتها 1.3 تريليون دولار. المقاولون وفرق المشتريات يواجهون تحديات متزايدة في إدارة سلاسل التوريد وتسعير المواد بدقة.'
            : 'Construction procurement in Saudi Arabia is experiencing exceptional growth with Vision 2030 projects exceeding $1.3 trillion. Contractors and procurement teams face increasing challenges in managing supply chains and pricing materials accurately.' }}</p>
        <p>{{ $isAr
            ? 'الحل الأمثل هو منصة مشتريات رقمية مبنية خصيصاً للسوق السعودي: تفهم متطلبات ضريبة القيمة المضافة، وأوقات استيراد المواد من أوروبا وآسيا، والفروق في الأسعار بين المناطق. كيمتا توفر هذا الحل مع فهرس يضم 418,326 منتجاً إنشائياً معتمداً.'
            : 'The optimal solution is a digital procurement platform built specifically for the Saudi market: understanding VAT requirements, import lead times from Europe and Asia, and regional price differences. Qimta provides this solution with an index of 418,326 verified construction products.' }}</p>
    </div>
</section>

{{-- FAQ --}}
<section class="lp-faq">
    <div class="container" style="max-width:800px;">
        <h2>{{ $isAr ? 'أسئلة شائعة' : 'Frequently Asked Questions' }}</h2>
        @foreach([
            ['q'=> $isAr ? 'ما هي منصة مشتريات الإنشاء؟' : 'What is a construction procurement platform?',
             'a'=> $isAr ? 'نظام رقمي يربط المقاولين وفرق المشتريات بموردي مواد البناء المعتمدين مع تسعير فوري وموثّق. كيمتا توفر هذا مجاناً مع 418,326 منتج.' : 'A digital system connecting contractors and procurement teams to verified building materials suppliers with instant, verified pricing. Qimta provides this free with 418,326 products.'],
            ['q'=> $isAr ? 'هل تعمل في دول الخليج الأخرى؟' : 'Does it work in other GCC countries?',
             'a'=> $isAr ? 'نعم، كيمتا تخدم السعودية والإمارات وقطر والكويت والبحرين وعُمان.' : 'Yes, Qimta serves Saudi Arabia, UAE, Qatar, Kuwait, Bahrain, and Oman.'],
            ['q'=> $isAr ? 'هل يمكن التكامل مع ERP؟' : 'Can it integrate with ERP systems?',
             'a'=> $isAr ? 'نعم، REST API متاح للتكامل مع معظم أنظمة ERP. تواصل مع فريق المؤسسات للحصول على وثائق التكامل.' : 'Yes, REST API is available for integration with most ERP systems. Contact the enterprise team for integration documentation.'],
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
        <h2>{{ $isAr ? 'ابدأ مشتريات الإنشاء الذكية اليوم' : 'Start Smart Construction Procurement Today' }}</h2>
        <p>{{ $isAr ? 'مجاني، فوري، مبني للسعودية والخليج' : 'Free, instant, built for Saudi Arabia and GCC' }}</p>
        <a href="{{ route('enduser.register') }}">{{ $isAr ? 'ابدأ مجاناً' : 'Get Started Free' }}</a>
    </div>
</section>

@endsection
