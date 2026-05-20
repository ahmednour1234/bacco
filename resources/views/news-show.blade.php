@extends('layouts.app')

@php
    $isAr  = app()->getLocale() === 'ar';
    $dir   = $isAr ? 'rtl' : 'ltr';
    $title = $isAr ? $article->title_ar : $article->title_en;
    $desc  = $isAr ? ($article->desc_ar ?? '') : ($article->desc_en ?? '');
    $cat   = $isAr ? ($article->name_ar ?? $article->name_en) : $article->name_en;
    $metaDesc = mb_substr(strip_tags($desc), 0, 155)
        ?: ($isAr
            ? 'اقرأ المقال كاملاً على منصة كيمتا لأخبار البناء والمشتريات في الخليج.'
            : 'Read the full article on Qimta — construction and procurement news for Saudi Arabia and GCC.');

    if (!function_exists('readTimeShow')) {
        function readTimeShow(string $html): int {
            $words = str_word_count(strip_tags($html));
            return max(1, (int) ceil($words / 200));
        }
    }
@endphp

@section('title', $title . ' | Qimta ' . $cat)
@section('description', $metaDesc)
@section('og_type', 'article')
{{-- Suppress AR hreflang when article has no Arabic content (AR requests redirect to EN) --}}
@if(empty($article->title_ar))
@section('no_ar_hreflang', '1')
@endif
@if($article->image)
@section('og_image', 'https://www.qimta.com' . Storage::disk('public')->url($article->image))
@endif

@push('schema')
@php
$_breadcrumb = json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>($isAr?'الرئيسية':'Home'),'item'=>'https://www.qimta.com/'],
        ['@type'=>'ListItem','position'=>2,'name'=>($isAr?'الأخبار':'News'),'item'=>'https://www.qimta.com/news'],
        ['@type'=>'ListItem','position'=>3,'name'=>$cat,'item'=>'https://www.qimta.com/news?category='.urlencode($article->name_en)],
        ['@type'=>'ListItem','position'=>4,'name'=>$title,'item'=>'https://www.qimta.com/news/'.$article->slug],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$_articleSchema = json_encode([
    '@context'         => 'https://schema.org',
    '@type'            => 'NewsArticle',
    'headline'         => $title,
    'description'      => mb_substr(strip_tags($desc), 0, 200),
    'image'            => $article->image ? 'https://www.qimta.com'.Storage::disk('public')->url($article->image) : 'https://www.qimta.com/images/qimta-og.jpg',
    'datePublished'    => $article->created_at?->toISOString(),
    'dateModified'     => $article->updated_at?->toISOString(),
    'author'           => ['@type'=>'Organization','name'=>'Qimta Technology Company','url'=>'https://www.qimta.com'],
    'publisher'        => ['@type'=>'Organization','name'=>'Qimta Technology Company','url'=>'https://www.qimta.com','logo'=>['@type'=>'ImageObject','url'=>'https://www.qimta.com/images/qimta-og.jpg']],
    'url'              => 'https://www.qimta.com/news/'.$article->slug,
    'inLanguage'       => $isAr ? 'ar' : 'en',
    'mainEntityOfPage' => ['@type'=>'WebPage','@id'=>'https://www.qimta.com/news/'.$article->slug],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $_breadcrumb !!}</script>
<script type="application/ld+json">{!! $_articleSchema !!}</script>
@endpush

@section('content')

    {{-- -- Breadcrumb ---------------------------------------------------- --}}
    <div class="ns-breadcrumb-bar">
        <div class="ns-container">
            <nav class="ns-breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">{{ $isAr ? 'الرئيسية' : 'HOME' }}</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="{{ route('news') }}">{{ $isAr ? 'الأخبار' : 'NEWS' }}</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="{{ route('news', ['category' => $article->name_en]) }}">{{ strtoupper($cat) }}</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="ns-bc-current" aria-current="page">{{ strtoupper($title) }}</span>
            </nav>
        </div>
    </div>

    {{-- -- Two-column layout ---------------------------------------------- --}}
    <div class="ns-container ns-layout">

        {{-- ---- Main Column ---- --}}
        <main class="ns-main">

            {{-- Category badge --}}
            <div class="ns-badge-row">
                <a href="{{ route('news', ['category' => $article->name_en]) }}" class="ns-badge">{{ strtoupper($cat) }}</a>
            </div>

            {{-- Title --}}
            <h1 class="ns-title">{{ $title }}</h1>

            {{-- Meta --}}
            <div class="ns-meta">
                <span class="ns-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    {{ strtoupper($cat) }}
                </span>
                <span class="ns-meta-dot">�</span>
                <span class="ns-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ readTimeShow($desc) }} {{ $isAr ? 'دقيقة قراءة' : 'min read' }}
                </span>
                <span class="ns-meta-dot">�</span>
                <span class="ns-meta-item">Qimta {{ $isAr ? 'فريق' : 'Team' }}</span>
                <div class="ns-lang-toggle" style="{{ $isAr ? 'margin-right:auto' : 'margin-left:auto' }}">
                    <a href="{{ route('news.show', $article->slug) }}" class="ns-lang {{ !$isAr ? 'active' : '' }}">EN</a>
                    <a href="{{ $article->title_ar ? route('ar.news.show', $article->slug) : route('news.show', $article->slug) }}" class="ns-lang {{ $isAr ? 'active' : '' }}">AR</a>
                </div>
            </div>

            {{-- Fact Block --}}
            <div dir="{{ $dir }}" style="font-size:13px;color:#555;line-height:1.85;border-left:3px solid #006a3b;padding:11px 16px;background:#f7fdf9;border-radius:0 8px 8px 0;margin:0 0 24px;">
                @if($isAr)
                    كيمتا منصة الذكاء الاصطناعي لتسعير جداول الكميات الإنشائية — تُفهرس {{ number_format($catalogStats['products']) }} منتجاً موثقاً عبر {{ $catalogStats['categories'] }} فئة و{{ $catalogStats['divisions'] }} قسم هندسي في السعودية والخليج العربي. يستخدمها المقاولون والمشترون للحصول على أسعار تنافسية خلال أقل من 60 ثانية. تغطي المنصة السوق السعودي والإماراتي والقطري والكويتي والبحريني والعُماني.
                @else
                    Qimta is Saudi Arabia's AI-powered BOQ pricing platform — indexing {{ number_format($catalogStats['products']) }} verified construction products across {{ $catalogStats['categories'] }} categories and {{ $catalogStats['divisions'] }} engineering divisions. Procurement teams in Saudi Arabia, UAE, Qatar, Kuwait, Bahrain, and Oman use Qimta to retrieve competitive BOQ pricing in under 60 seconds.
                @endif
            </div>

            {{-- Hero image --}}
            @if($article->image)
            <div class="ns-hero">
                <img src="{{ Storage::disk('public')->url($article->image) }}" alt="{{ $title }}" class="ns-hero-img">
                <div class="ns-hero-overlay">
                    <div class="ns-play-btn">
                        <svg width="22" height="22" fill="white" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </div>
            </div>
            @endif

            {{-- Article body --}}
            <div class="ns-article-body" dir="{{ $dir }}">
                {!! $desc !!}
            </div>

            {{-- Internal contextual links --}}
            <div dir="{{ $dir }}" style="margin:32px 0 8px;padding:20px 22px;background:#f5f5f5;border-radius:10px;">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#888;margin:0 0 12px;">{{ $isAr ? 'استكشف على كيمتا' : 'Explore on Qimta' }}</p>
                <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px;">
                    <li><a href="{{ url('/catalog') }}" style="color:#006a3b;text-decoration:none;font-size:14px;">{{ $isAr ? 'كتالوج مواد البناء — ' . number_format($catalogStats['products']) . ' منتج موثق' : 'Construction Materials Catalog — ' . number_format($catalogStats['products']) . ' Verified Products' }}</a></li>
                    <li><a href="{{ url('/for-brands') }}" style="color:#006a3b;text-decoration:none;font-size:14px;">{{ $isAr ? 'أدرج منتجاتك على كيمتا — للعلامات التجارية والموردين' : 'List Your Products on Qimta — For Brands &amp; Manufacturers' }}</a></li>
                    <li><a href="{{ url('/about') }}" style="color:#006a3b;text-decoration:none;font-size:14px;">{{ $isAr ? 'عن منصة كيمتا لتسعير مواد البناء' : 'About Qimta — AI Construction Pricing Platform' }}</a></li>
                    <li><a href="{{ route('news') }}" style="color:#006a3b;text-decoration:none;font-size:14px;">{{ $isAr ? 'جميع أخبار البناء والمشتريات في الخليج' : 'All Construction &amp; Procurement News' }}</a></li>
                </ul>
            </div>

            {{-- Share --}}
            <div class="ns-share-bar">
                <span class="ns-share-label">{{ $isAr ? 'شارك هذا المقال' : 'Share this article' }}</span>
                <div class="ns-share-btns">
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($title) }}&url={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener" class="ns-share-btn ns-share-x">Twitter / X</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener" class="ns-share-btn ns-share-li">LinkedIn</a>
                </div>
            </div>

            {{-- Back link --}}
            <div class="ns-back-row">
                <a href="{{ route('news') }}" class="ns-back-link" style="{{ $isAr ? 'flex-direction:row-reverse' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"
                         style="{{ $isAr ? 'transform:scaleX(-1)' : '' }}"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    {{ $isAr ? 'العودة إلى الأخبار' : 'Back to News' }}
                </a>
            </div>

        </main>

        {{-- ---- Sidebar ---- --}}
        <aside class="ns-sidebar">

            {{-- Related Reading --}}
            @if($related->count())
            <div class="ns-sidebar-card">
                <p class="ns-sidebar-heading">{{ $isAr ? 'مقالات ذات صلة' : 'Related Reading' }}</p>
                @php $byCategory = $related->groupBy('name_en'); @endphp
                @foreach($byCategory as $catName => $items)
                <div class="ns-related-group">
                    <span class="ns-related-cat-label">{{ $catName }}</span>
                    @foreach($items as $rel)
                    <a href="{{ route('news.show', $rel->slug) }}" class="ns-related-link">
                        {{ $isAr ? $rel->title_ar : $rel->title_en }}
                    </a>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif

            {{-- CTA Card --}}
            <div class="ns-cta-card">
                <p class="ns-cta-heading">{{ $isAr ? 'هل أنت مستعد للأتمتة؟' : 'Ready to automate?' }}</p>
                <p class="ns-cta-sub">{{ $isAr ? 'اختبر مستقبل المشتريات الإنشائية مع محرك الذكاء الاصطناعي لدينا.' : 'Experience the future of construction procurement with our AI-driven engine.' }}</p>
                <a href="{{ route('enduser.register') }}" class="ns-cta-btn">
                    {{ $isAr ? 'جرّب محرك التسعير' : 'Try the pricing engine' }}
                </a>
            </div>

            {{-- Explore links --}}
            <div class="ns-sidebar-card">
                <p class="ns-sidebar-heading">{{ $isAr ? 'استكشف المنصة' : 'Explore Qimta' }}</p>
                <a href="{{ route('catalog.index') }}" class="ns-related-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-{{ $isAr ? 'left' : 'right' }}:6px"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    {{ $isAr ? 'كتالوج مواد البناء — ' . number_format($catalogStats['products']) . ' منتج' : 'Construction Materials Catalog — ' . number_format($catalogStats['products']) . ' Products' }}
                </a>
                <a href="{{ route('for-brands') }}" class="ns-related-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-{{ $isAr ? 'left' : 'right' }}:6px"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                    {{ $isAr ? 'أدرج علامتك التجارية على كيمتا' : 'List Your Brand on Qimta' }}
                </a>
                <a href="{{ route('about') }}" class="ns-related-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-{{ $isAr ? 'left' : 'right' }}:6px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $isAr ? 'عن كيمتا — كيف نعمل' : 'About Qimta — How We Work' }}
                </a>
                <a href="{{ route('contact') }}" class="ns-related-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-{{ $isAr ? 'left' : 'right' }}:6px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    {{ $isAr ? 'تواصل مع فريق كيمتا' : 'Contact the Qimta Team' }}
                </a>
            </div>

        </aside>

    </div>

</div>

<style>
.ns-page { background:#f7f7f5; min-height:100vh; font-family:'Cairo', sans-serif; }
.ns-container { max-width:1100px; margin:0 auto; padding:0 28px; }

/* Breadcrumb */
.ns-breadcrumb-bar { background:#fff; border-bottom:1px solid #e8e8e5; padding:11px 0; }
.ns-breadcrumb { display:flex; align-items:center; flex-wrap:wrap; gap:4px; font-size:11px; font-weight:600; letter-spacing:.06em; color:#999; text-transform:uppercase; }
.ns-breadcrumb a { color:#999; text-decoration:none; transition:color .15s; }
.ns-breadcrumb a:hover { color:#006A3B; }
.ns-bc-sep { color:#bbb; margin:0 2px; font-size:13px; }
.ns-bc-current { color:#333; }

/* Layout */
.ns-layout { display:grid; grid-template-columns:1fr 300px; gap:40px; padding-top:36px; padding-bottom:80px; align-items:start; }
@media(max-width:840px) { .ns-layout { grid-template-columns:1fr; } .ns-sidebar { order:1; position:static; } }

/* Badge */
.ns-badge-row { margin-bottom:14px; }
.ns-badge { display:inline-block; background:#006A3B; color:#fff; font-size:10.5px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; padding:5px 13px; border-radius:5px; text-decoration:none; transition:background .15s; }
.ns-badge:hover { background:#004d2a; }

/* Title */
.ns-title { font-size:clamp(1.55rem,3.5vw,2.2rem); font-weight:800; color:#111; line-height:1.25; margin:0 0 18px; font-family:'Cairo', sans-serif; }

/* Meta */
.ns-meta { display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-size:13px; color:#888; padding-bottom:22px; border-bottom:1px solid #e8e8e5; margin-bottom:24px; }
.ns-meta-item { display:flex; align-items:center; gap:5px; }
.ns-meta-dot { color:#ccc; }
.ns-lang-toggle { display:flex; gap:4px; }
.ns-lang { font-size:12px; font-weight:600; padding:3px 11px; border-radius:20px; text-decoration:none; background:#efefef; color:#555; transition:background .15s,color .15s; }
.ns-lang.active { background:#006A3B; color:#fff; }

/* Hero */
.ns-hero { position:relative; border-radius:14px; overflow:hidden; margin-bottom:32px; max-height:420px; background:#1b263b; box-shadow:0 4px 24px rgba(0,0,0,.1); }
.ns-hero-img { width:100%; height:420px; object-fit:cover; display:block; }
.ns-hero-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(0,0,0,.45) 0%,rgba(0,0,0,.1) 60%,transparent 100%); display:flex; align-items:center; justify-content:center; }
.ns-play-btn { width:60px; height:60px; background:rgba(255,255,255,.18); border:2px solid rgba(255,255,255,.55); border-radius:50%; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(6px); cursor:pointer; transition:background .2s,transform .2s; }
.ns-play-btn:hover { background:rgba(255,255,255,.28); transform:scale(1.08); }

/* Article body */
.ns-article-body { font-size:16px; line-height:1.9; color:#2d2d2d; font-family:'Cairo', sans-serif; }
.ns-article-body h1 { font-size:1.9rem; font-weight:800; margin:2rem 0 .8rem; color:#111; }
.ns-article-body h2 { font-size:1.4rem; font-weight:700; margin:1.8rem 0 .7rem; color:#111; border-bottom:1px solid #e8e8e5; padding-bottom:8px; }
.ns-article-body h3 { font-size:1.15rem; font-weight:700; margin:1.5rem 0 .6rem; color:#1a1a1a; }
.ns-article-body h4 { font-size:1rem; font-weight:600; margin:1.2rem 0 .5rem; color:#333; }
.ns-article-body p  { margin-bottom:1.1rem; }
.ns-article-body ul { list-style:disc; padding-left:1.6rem; margin:.8rem 0 1.1rem; }
.ns-article-body ol { list-style:decimal; padding-left:1.6rem; margin:.8rem 0 1.1rem; }
.ns-article-body li { margin-bottom:.45rem; }
.ns-article-body a  { color:#006A3B; text-decoration:underline; }
.ns-article-body a:hover { color:#004d2a; }
.ns-article-body strong,.ns-article-body b { font-weight:700; }
.ns-article-body em,.ns-article-body i { font-style:italic; }
.ns-article-body blockquote { border-left:4px solid #006A3B; margin:1.8rem 0; padding:14px 20px; background:rgba(0,106,59,.05); border-radius:0 10px 10px 0; font-style:italic; color:#555; }
.ns-article-body pre { background:#f4f4f4; border:1px solid #e0e0e0; border-radius:10px; padding:18px; overflow-x:auto; font-family:monospace; font-size:14px; margin:1.2rem 0; }
.ns-article-body img  { max-width:100%; border-radius:12px; margin:1.2rem 0; }
.ns-article-body video { max-width:100%; border-radius:12px; margin:1.2rem 0; }
.ns-article-body table { width:100%; border-collapse:collapse; margin:1rem 0; font-size:14px; }
.ns-article-body th,.ns-article-body td { border:1px solid #e0e0e0; padding:10px 14px; text-align:start; }
.ns-article-body th { background:#f8f9fa; font-weight:700; }
[dir="rtl"] .ns-article-body ul,[dir="rtl"] .ns-article-body ol { padding-left:0; padding-right:1.6rem; }
[dir="rtl"] .ns-article-body blockquote { border-left:none; border-right:4px solid #006A3B; border-radius:10px 0 0 10px; }

/* Share */
.ns-share-bar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-top:48px; padding:20px 24px; background:#fff; border-radius:12px; border:1px solid #e8e8e5; }
.ns-share-label { font-size:14px; font-weight:600; color:#333; }
.ns-share-btns { display:flex; gap:10px; }
.ns-share-btn { padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; transition:opacity .15s; }
.ns-share-btn:hover { opacity:.85; }
.ns-share-x  { background:#1a1a1a; color:#fff; }
.ns-share-li { background:#0077b5; color:#fff; }

/* Back */
.ns-back-row { margin-top:32px; }
.ns-back-link { display:inline-flex; align-items:center; gap:7px; font-size:14px; font-weight:600; color:#006A3B; text-decoration:none; transition:gap .15s; }
.ns-back-link:hover { gap:11px; }

/* Sidebar */
.ns-sidebar { display:flex; flex-direction:column; gap:20px; position:sticky; top:88px; }
.ns-sidebar-card { background:#fff; border:1px solid #e8e8e5; border-radius:14px; padding:20px; }
.ns-sidebar-heading { font-size:12px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#999; margin-bottom:16px; }
.ns-related-group { margin-bottom:18px; }
.ns-related-group:last-child { margin-bottom:0; }
.ns-related-cat-label { display:inline-block; font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#006A3B; margin-bottom:8px; }
.ns-related-link { display:block; font-size:13.5px; font-weight:600; color:#1a1a1a; line-height:1.45; margin-bottom:10px; text-decoration:none; transition:color .15s; }
.ns-related-link:last-child { margin-bottom:0; }
.ns-related-link:hover { color:#006A3B; }

/* CTA */
.ns-cta-card { background:#0d1b2a; border-radius:14px; padding:24px 20px; color:#fff; }
.ns-cta-heading { font-size:17px; font-weight:800; margin-bottom:8px; line-height:1.3; }
.ns-cta-sub { font-size:13px; color:rgba(255,255,255,.65); line-height:1.6; margin-bottom:18px; }
.ns-cta-btn { display:block; text-align:center; background:#006A3B; color:#fff; font-size:13.5px; font-weight:700; padding:11px 18px; border-radius:9px; text-decoration:none; transition:background .15s; }
.ns-cta-btn:hover { background:#005530; }
</style>
@endsection
