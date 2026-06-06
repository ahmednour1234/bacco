@extends('layouts.app')

@php 
    $isAr   = app()->getLocale() === 'ar'; 
    $__rp   = $isAr ? 'ar.' : '';
    $_p     = number_format($catalogStats['products']);
    $_cats  = $catalogStats['categories'];
    $_brands = $catalogStats['brands'];
@endphp

@section('title', $isAr
    ? 'تسعير BOQ — جداول الكميات الإنشائية | كيمتا | السعودية والخليج'
    : 'BOQ Pricing — Construction Bill of Quantities | Qimta | Saudi Arabia & GCC')

@section('description', $isAr
    ? 'سعّر جدول الكميات بالكامل خلال 60 ثانية. كيمتا تُطابق كل بند مع ' . $_p . ' منتج إنشائي معتمد — مجاناً للمقاولين وفرق المشتريات في السعودية والخليج.'
    : 'Price your entire Bill of Quantities in 60 seconds. Qimta matches every line item against ' . $_p . ' verified construction products — free for contractors and procurement teams in Saudi Arabia and GCC.')

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
    .lp-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-top: 48px; }
    .lp-step { text-align: center; padding: 32px 24px; border: 1.5px solid var(--border); border-radius: 16px; }
    .lp-step-num { width: 44px; height: 44px; border-radius: 50%; background: var(--green); color: #fff; font-weight: 900; font-size: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    .lp-step h3 { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
    .lp-step p { font-size: 14px; color: #555; line-height: 1.7; margin: 0; }
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
        .lp-stats-grid, .lp-steps { grid-template-columns: repeat(2, 1fr); }
    }
    @media(max-width: 480px) {
        .lp-steps { grid-template-columns: 1fr; }
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
                ['@type'=>'ListItem','position'=>2,'name'=>$isAr ? 'تسعير BOQ' : 'BOQ Pricing','item'=>'https://qimta.com' . request()->getPathInfo()],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'ما هو BOQ وكيف يُسعَّر؟' : 'What is a BOQ and how is it priced?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'BOQ (Bill of Quantities) هو وثيقة تُدرج كل مواد ومنتجات المشروع الإنشائي مع الكميات. تسعيره يعني إيجاد السعر الحالي لكل بند. كيمتا تُسعّر الـBOQ تلقائياً في أقل من 60 ثانية من خلال مطابقة كل بند مع ' . $_p . ' منتج معتمد.'
                        : 'A BOQ (Bill of Quantities) lists all materials and products for a construction project with quantities. Pricing it means finding current market prices for each item. Qimta auto-prices BOQs in under 60 seconds by matching each line against ' . $_p . ' verified products.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'ما الفرق بين تسعير BOQ اليدوي والآلي؟' : 'What is the difference between manual and automated BOQ pricing?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'التسعير اليدوي يستغرق 3-10 أيام ويعتمد على اتصالات مع موردين وعروض أسعار متعددة. التسعير الآلي بكيمتا يستغرق أقل من 60 ثانية بدقة 99.9% من قواعد بيانات المصنّعين المباشرة.'
                        : 'Manual BOQ pricing takes 3-10 days relying on supplier calls and multiple quotes. Automated pricing with Qimta takes under 60 seconds with 99.9% accuracy from direct manufacturer databases.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'هل تدعم كيمتا ملفات Excel وPDF للـ BOQ؟' : 'Does Qimta support Excel and PDF BOQ files?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'نعم، تدعم كيمتا ملفات Excel (.xlsx, .xls) وPDF وCSV ومعظم تنسيقات BOQ الشائعة. يعالج المحلل التلقائي الهياكل المختلفة دون إعداد مسبق.'
                        : 'Yes, Qimta supports Excel (.xlsx, .xls), PDF, CSV and most common BOQ formats. The automatic parser handles varied structures without any setup.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => $isAr ? 'كم تكلفة تسعير BOQ على كيمتا؟' : 'How much does BOQ pricing cost on Qimta?',
                    'acceptedAnswer' => ['@type'=>'Answer','text' => $isAr
                        ? 'تسعير BOQ مجاني تماماً للمشترين والمقاولين وفرق المشتريات — بلا حد لعدد البنود أو المشاريع. خطط المؤسسات توفر ميزات إضافية مثل المعالجة الدفعية والـ API.'
                        : 'BOQ pricing is completely free for buyers, contractors, and procurement teams — with no limit on line items or projects. Enterprise plans offer additional features like batch processing and API access.'],
                ],
            ],
        ],
        [
            '@type' => 'WebPage',
            'name' => $isAr ? 'تسعير BOQ — كيمتا' : 'BOQ Pricing — Qimta',
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
        <p class="lp-eyebrow">{{ $isAr ? 'تسعير جداول الكميات' : 'Bill of Quantities Pricing' }}</p>
        <h1>{{ $isAr ? 'سعّر جدول كمياتك في 60 ثانية' : 'Price Your BOQ in 60 Seconds' }}</h1>
        <p>{{ $isAr
            ? 'ارفع جدول كمياتك — كيمتا تُطابق كل بند مع ' . $_p . ' منتج إنشائي معتمد وتُرجع أسعاراً موثّقة من المصنّعين مباشرةً. مجاناً للمقاولين وفرق المشتريات.'
            : 'Upload your BOQ — Qimta matches every line item against ' . $_p . ' verified construction products and returns prices verified directly from manufacturers. Free for contractors and procurement teams.' }}</p>
        <div class="lp-cta">
            <a href="{{ route('guest.boq.create') }}" class="btn-primary">{{ $isAr ? 'سعّر جدول كمياتي' : 'Price My BOQ' }}</a>
            <a href="{{ route($__rp . 'catalog.index') }}" class="btn-outline">{{ $isAr ? 'تصفح الكتالوج' : 'Browse Catalog' }}</a>
        </div>
    </div>
</section>

{{-- STATS --}}
<section class="lp-stats">
    <div class="container">
        <div class="lp-stats-grid">
            <div class="lp-stat">
                <div class="lp-stat-val" content="60">&lt;60s</div>
                <div class="lp-stat-label">{{ $isAr ? 'لتسعير جدول الكميات بالكامل' : 'To price a full BOQ' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" content="{{ $catalogStats['products'] }}">{{ $_p }}</div>
                <div class="lp-stat-label">{{ $isAr ? 'منتج إنشائي معتمد' : 'Verified products in database' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" content="99.9">99.9%</div>
                <div class="lp-stat-label">{{ $isAr ? 'دقة محرك المطابقة RAG' : 'RAG engine accuracy' }}</div>
            </div>
            <div class="lp-stat">
                <div class="lp-stat-val" content="80">80%</div>
                <div class="lp-stat-label">{{ $isAr ? 'توفير في وقت دورة المشتريات' : 'Procurement cycle time saved' }}</div>
            </div>
        </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="lp-section">
    <div class="container">
        <h2 style="text-align:center;">{{ $isAr ? 'كيف يعمل تسعير BOQ على كيمتا؟' : 'How Does BOQ Pricing Work on Qimta?' }}</h2>
        <p style="text-align:center;color:#555;max-width:600px;margin:0 auto 8px;">
            {{ $isAr ? 'ثلاث خطوات — من الرفع إلى الأسعار المعتمدة' : 'Three steps — from upload to verified prices' }}
        </p>
        <div class="lp-steps">
            <div class="lp-step">
                <div class="lp-step-num">1</div>
                <h3>{{ $isAr ? 'ارفع جدول الكميات' : 'Upload Your BOQ' }}</h3>
                <p>{{ $isAr ? 'Excel أو PDF أو CSV — يعالج المحلل التلقائي أي هيكل بدون إعداد مسبق.' : 'Excel, PDF, or CSV — the automatic parser handles any structure without setup.' }}</p>
            </div>
            <div class="lp-step">
                <div class="lp-step-num">2</div>
                <h3>{{ $isAr ? 'المطابقة التلقائية' : 'Automatic Matching' }}</h3>
                <p>{{ $isAr ? 'محرك RAG يُطابق كل بند مع ' . $_p . ' منتج معتمد من قواعد بيانات المصنّعين المباشرة.' : 'RAG engine matches every line item against ' . $_p . ' verified products from direct manufacturer databases.' }}</p>
            </div>
            <div class="lp-step">
                <div class="lp-step-num">3</div>
                <h3>{{ $isAr ? 'تصدير الأسعار' : 'Export Prices' }}</h3>
                <p>{{ $isAr ? 'احصل على جدول كمياتك مُسعَّراً بالكامل مع المصادر ومستعداً للتقديم.' : 'Get your fully priced BOQ with sources, ready for submission.' }}</p>
            </div>
        </div>
    </div>
</section>

{{-- LONG-FORM --}}
<section class="lp-section" style="background:#f8faf8;padding:72px 0;">
    <div class="container" style="max-width:860px;">
        <h2>{{ $isAr ? 'دليل تسعير BOQ في السعودية والخليج' : 'Guide to BOQ Pricing in Saudi Arabia and GCC' }}</h2>

        <p>{{ $isAr
            ? 'جدول الكميات (BOQ) هو العمود الفقري لأي مشروع إنشائي. يُدرج كل مادة وعنصر ومنتج مطلوب في المشروع مع كمياته الدقيقة. تسعير هذا الجدول بدقة وسرعة يُحدد هامش ربح المقاول، وقدرة المشتري على التفاوض، وجدوى المشروع ككل.'
            : 'A Bill of Quantities (BOQ) is the backbone of any construction project. It lists every material, element, and product required with exact quantities. Pricing this document accurately and quickly determines a contractor\'s profit margin, a buyer\'s negotiating power, and the overall project viability.' }}</p>

        <p>{{ $isAr
            ? 'في السعودية والخليج، تسعير BOQ يواجه تحديات فريدة: تقلبات أسعار الصلب، ضريبة القيمة المضافة 15%، أوقات استيراد المواد الأجنبية، وفروق الأسعار بين الرياض وجدة والمنطقة الشرقية. هذه التحديات تجعل التسعير اليدوي غير موثوق وبطيئاً.'
            : 'In Saudi Arabia and GCC, BOQ pricing faces unique challenges: steel price volatility, 15% VAT, foreign materials import lead times, and price differences between Riyadh, Jeddah, and the Eastern Province. These challenges make manual pricing unreliable and slow.' }}</p>

        <p>{{ $isAr
            ? 'كيمتا تحل هذه التحديات بفهرسة ' . $_p . ' منتجاً إنشائياً من ' . $_brands . ' علامة تجارية معتمدة، مُعايَرة وفق أسعار السوق السعودي الفعلية. محرك RAG يُطابق كل بند في جدول الكميات مع المنتج الأدق مطابقة من قاعدة البيانات ويُرجع السعر الموثّق في أقل من 60 ثانية.'
            : 'Qimta solves these challenges by indexing ' . $_p . ' construction products from ' . $_brands . ' verified brands, calibrated to actual Saudi market rates. The RAG engine matches every BOQ line item with the most accurate product from the database and returns the verified price in under 60 seconds.' }}</p>

        <h2 style="margin-top:48px;">{{ $isAr ? 'فئات BOQ الأكثر طلباً' : 'Most Requested BOQ Categories' }}</h2>
        <p>{{ $isAr
            ? 'من خلال تحليل آلاف جداول الكميات في السعودية والخليج، الفئات الأكثر طلباً تشمل: الهياكل الفولاذية، أنظمة العزل الحراري، الواجهات الزجاجية، الأنظمة الكهربائية والميكانيكية، أنظمة الصرف الصحي، ومواد التشطيب. كيمتا تغطي جميع هذه الفئات عبر ' . $_cats . ' قسماً إنشائياً.'
            : 'From analyzing thousands of BOQs across Saudi Arabia and GCC, the most requested categories include: steel structures, thermal insulation systems, glass facades, electrical and mechanical systems, drainage systems, and finishing materials. Qimta covers all these categories across ' . $_cats . ' construction divisions.' }}</p>
    </div>
</section>

{{-- FAQ --}}
<section class="lp-faq">
    <div class="container" style="max-width:800px;">
        <h2>{{ $isAr ? 'أسئلة شائعة عن تسعير BOQ' : 'Frequently Asked Questions — BOQ Pricing' }}</h2>
        @foreach([
            ['q'=> $isAr ? 'ما هو BOQ وكيف يُسعَّر؟' : 'What is a BOQ and how is it priced?',
             'a'=> $isAr ? 'BOQ (جدول الكميات) يُدرج كل مواد المشروع الإنشائي. كيمتا تُسعّره تلقائياً بمطابقة كل بند مع ' . $_p . ' منتج معتمد في أقل من 60 ثانية.' : 'A BOQ (Bill of Quantities) lists all materials for a construction project. Qimta prices it automatically by matching every line against ' . $_p . ' verified products in under 60 seconds.'],
            ['q'=> $isAr ? 'هل تسعير BOQ مجاني؟' : 'Is BOQ pricing free?',
             'a'=> $isAr ? 'نعم، مجاني تماماً بلا حد لعدد البنود أو المشاريع للمشترين والمقاولين وفرق المشتريات.' : 'Yes, completely free with no limit on line items or projects for buyers, contractors, and procurement teams.'],
            ['q'=> $isAr ? 'ما الصيغ المدعومة للـ BOQ؟' : 'What BOQ formats are supported?',
             'a'=> $isAr ? 'Excel (.xlsx, .xls) وPDF وCSV ومعظم صيغ BOQ الشائعة.' : 'Excel (.xlsx, .xls), PDF, CSV, and most common BOQ formats.'],
            ['q'=> $isAr ? 'ما دقة التسعير؟' : 'How accurate is the pricing?',
             'a'=> $isAr ? 'دقة 99.9% من خلال المقارنة مع أكثر من مليار مواصفة تقنية للمصنّعين مع تحديثات فورية.' : '99.9% accuracy by cross-referencing 1B+ manufacturer technical specifications with real-time updates.'],
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
        <h2>{{ $isAr ? 'سعّر جدول كمياتك الآن — مجاناً' : 'Price Your BOQ Now — Free' }}</h2>
        <p>{{ $isAr ? 'بلا تسجيل مطوّل، بلا حد للبنود، نتائج فورية' : 'No lengthy signup, no line item limits, instant results' }}</p>
        <a href="{{ route('guest.boq.create') }}">{{ $isAr ? 'ابدأ مجاناً' : 'Get Started Free' }}</a>
    </div>
</section>

@endsection
