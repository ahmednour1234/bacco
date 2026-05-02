@extends('layouts.app')

@php
    $isAr  = app()->getLocale() === 'ar';
    $dir   = $isAr ? 'rtl' : 'ltr';
    $title = $isAr ? $article->title_ar : $article->title_en;
    $desc  = $isAr ? ($article->desc_ar ?? '') : ($article->desc_en ?? '');
    $cat   = $isAr ? ($article->name_ar ?? $article->name_en) : $article->name_en;

    function readTimeShow(string $html): int {
        $words = str_word_count(strip_tags($html));
        return max(1, (int) ceil($words / 200));
    }
@endphp

@section('title', $title)

@section('content')
<div class="ns-page" dir="{{ $dir }}">

    {{-- ── Breadcrumb ──────────────────────────────────────────────────── --}}
    <div class="ns-breadcrumb-bar">
        <div class="ns-container">
            <nav class="ns-breadcrumb">
                <a href="{{ url('/') }}">{{ $isAr ? 'الرئيسية' : 'HOME' }}</a>
                <span class="ns-bc-sep">›</span>
                <a href="{{ route('news') }}">{{ $isAr ? 'الأخبار' : 'NEWS' }}</a>
                <span class="ns-bc-sep">›</span>
                <a href="{{ route('news', ['category' => $article->name_en]) }}">{{ strtoupper($cat) }}</a>
                <span class="ns-bc-sep">›</span>
                <span class="ns-bc-current">{{ strtoupper($title) }}</span>
            </nav>
        </div>
    </div>

    {{-- ── Two-column layout ────────────────────────────────────────────── --}}
    <div class="ns-container ns-layout">

        {{-- ════ Main Column ════ --}}
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
                <span class="ns-meta-dot">•</span>
                <span class="ns-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ readTimeShow($desc) }} {{ $isAr ? 'دقيقة قراءة' : 'min read' }}
                </span>
                <span class="ns-meta-dot">•</span>
                <span class="ns-meta-item">Qimta {{ $isAr ? 'الفريق' : 'Team' }}</span>
                <div class="ns-lang-toggle" style="{{ $isAr ? 'margin-right:auto' : 'margin-left:auto' }}">
                    <a href="{{ route('locale.switch', 'en') }}" class="ns-lang {{ !$isAr ? 'active' : '' }}">EN</a>
                    <a href="{{ route('locale.switch', 'ar') }}" class="ns-lang {{ $isAr ? 'active' : '' }}">AR</a>
                </div>
            </div>

            {{-- Hero image --}}
            @if($article->image)
            <div class="ns-hero">
                <img src="{{ Storage::url($article->image) }}" alt="{{ $title }}" class="ns-hero-img">
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

        {{-- ════ Sidebar ════ --}}
        <aside class="ns-sidebar">

            {{-- Related Reading --}}
            @if($related->count())
            <div class="ns-sidebar-card">
                <p class="ns-sidebar-heading">{{ $isAr ? 'قراءة ذات صلة' : 'Related Reading' }}</p>
                @php $byCategory = $related->groupBy('name_en'); @endphp
                @foreach($byCategory as $catName => $items)
                <div class="ns-related-group">
                    <span class="ns-related-cat-label">{{ $catName }}</span>
                    @foreach($items as $rel)
                    <a href="{{ route('news.show', $rel->uuid) }}" class="ns-related-link">
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
                <p class="ns-cta-sub">{{ $isAr ? 'اختبر مستقبل المشتريات بمحرك الذكاء الاصطناعي.' : 'Experience the future of construction procurement with our AI-driven engine.' }}</p>
                <a href="{{ route('news') }}" class="ns-cta-btn">
                    {{ $isAr ? 'جرّب محرك التسعير' : 'Try the pricing engine' }}
                </a>
            </div>

        </aside>

    </div>

</div>

<style>
.ns-page { background:#f7f7f5; min-height:100vh; font-family:'Cairo','Inter',sans-serif; }
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
@media(max-width:840px) { .ns-layout { grid-template-columns:1fr; } .ns-sidebar { order:-1; } }

/* Badge */
.ns-badge-row { margin-bottom:14px; }
.ns-badge { display:inline-block; background:#006A3B; color:#fff; font-size:10.5px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; padding:5px 13px; border-radius:5px; text-decoration:none; transition:background .15s; }
.ns-badge:hover { background:#004d2a; }

/* Title */
.ns-title { font-size:clamp(1.55rem,3.5vw,2.2rem); font-weight:800; color:#111; line-height:1.25; margin:0 0 18px; font-family:'Cairo',sans-serif; }

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
.ns-article-body { font-size:16px; line-height:1.9; color:#2d2d2d; font-family:'Cairo','Inter',sans-serif; }
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
