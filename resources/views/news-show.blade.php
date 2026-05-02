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
<div style="background:#f8f9fa; min-height:100vh;" dir="{{ $dir }}">

    {{-- ── Breadcrumb ──────────────────────────────────────────────────── --}}
    <div style="background:#fff; border-bottom:1px solid #eee; padding:10px 0;">
        <div style="max-width:860px; margin:0 auto; padding:0 24px;">
            <nav style="font-size:13px; color:#888; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <a href="{{ url('/') }}" style="color:#888; text-decoration:none;">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="{{ $isAr ? 'transform:scaleX(-1)' : '' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('news') }}" style="color:#888; text-decoration:none;">{{ $isAr ? 'الأخبار' : 'News' }}</a>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="{{ $isAr ? 'transform:scaleX(-1)' : '' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span style="color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:280px;">{{ $title }}</span>
            </nav>
        </div>
    </div>

    {{-- ── Article ──────────────────────────────────────────────────────── --}}
    <div style="max-width:860px; margin:0 auto; padding:48px 24px 80px;">

        {{-- Header --}}
        <div style="margin-bottom:32px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                <a href="{{ route('news', ['category' => $article->name_en]) }}"
                   style="background:#006A3B; color:#fff; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:5px 12px; border-radius:5px; text-decoration:none;">
                    {{ $cat }}
                </a>
                <span style="background:#f0f0f0; color:#666; font-size:11px; font-weight:600; padding:5px 12px; border-radius:5px; letter-spacing:.05em;">
                    EN | AR
                </span>
            </div>

            <h1 style="font-size:clamp(1.5rem,4vw,2.4rem); font-weight:800; color:#111; margin:0 0 16px; line-height:1.2; font-family:'Cairo',sans-serif;">
                {{ $title }}
            </h1>

            <div style="display:flex; align-items:center; gap:20px; font-size:13px; color:#888; padding-bottom:24px; border-bottom:1px solid #eee; flex-wrap:wrap;">
                <span style="display:flex; align-items:center; gap:5px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $article->created_at->format('d M Y') }}
                </span>
                <span style="display:flex; align-items:center; gap:5px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ readTimeShow($desc) }} {{ $isAr ? 'دقيقة قراءة' : 'min read' }}
                </span>
                {{-- Language toggle --}}
                <div style="display:flex; align-items:center; gap:6px; margin-{{ $isAr ? 'right' : 'left' }}:auto;">
                    <a href="{{ route('locale.switch', 'en') }}"
                       style="font-size:12px; font-weight:600; padding:4px 12px; border-radius:20px; text-decoration:none; transition:.15s;
                              {{ !$isAr ? 'background:#006A3B;color:#fff;' : 'background:#f0f0f0;color:#555;' }}">EN</a>
                    <a href="{{ route('locale.switch', 'ar') }}"
                       style="font-size:12px; font-weight:600; padding:4px 12px; border-radius:20px; text-decoration:none; transition:.15s;
                              {{ $isAr ? 'background:#006A3B;color:#fff;' : 'background:#f0f0f0;color:#555;' }}">AR</a>
                </div>
            </div>
        </div>

        {{-- Featured image --}}
        @if($article->image)
        <div style="margin-bottom:32px; border-radius:16px; overflow:hidden; max-height:420px;">
            <img src="{{ Storage::url($article->image) }}" alt="{{ $title }}"
                 style="width:100%; height:420px; object-fit:cover; display:block;">
        </div>
        @endif

        {{-- Article body --}}
        <div class="article-content" dir="{{ $dir }}">
            {!! $desc !!}
        </div>

        {{-- Share --}}
        <div style="margin-top:48px; padding:24px; background:#fff; border-radius:12px; border:1px solid #efefef; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <p style="font-size:14px; font-weight:600; color:#333; margin:0;">
                {{ $isAr ? 'شارك هذا المقال' : 'Share this article' }}
            </p>
            <div style="display:flex; gap:10px;">
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($title) }}&url={{ urlencode(url()->current()) }}"
                   target="_blank" rel="noopener"
                   style="padding:8px 16px; background:#1da1f2; color:#fff; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">Twitter / X</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                   target="_blank" rel="noopener"
                   style="padding:8px 16px; background:#0077b5; color:#fff; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">LinkedIn</a>
            </div>
        </div>

        {{-- Related --}}
        @if($related->count())
        <div style="margin-top:56px;">
            <h3 style="font-size:18px; font-weight:800; color:#111; margin:0 0 20px; font-family:'Cairo',sans-serif;">
                {{ $isAr ? 'مقالات ذات صلة' : 'Related Articles' }}
            </h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px;">
                @foreach($related as $rel)
                <a href="{{ route('news.show', $rel->uuid) }}"
                   style="display:block; text-decoration:none; border-radius:12px; background:#fff; overflow:hidden; border:1px solid #efefef; transition:transform .2s, box-shadow .2s;"
                   onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,.1)'"
                   onmouseleave="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <div style="height:130px; background:linear-gradient(135deg,#0d1b2a,#1b263b); overflow:hidden;">
                        @if($rel->image)
                            <img src="{{ Storage::url($rel->image) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                        @endif
                    </div>
                    <div style="padding:14px;">
                        <h4 style="font-size:13px; font-weight:700; color:#111; margin:0 0 6px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                            {{ $isAr ? $rel->title_ar : $rel->title_en }}
                        </h4>
                        <p style="font-size:11px; color:#999; margin:0;">{{ $rel->created_at->format('d M Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Back link --}}
        <div style="margin-top:40px;">
            <a href="{{ route('news') }}"
               style="display:inline-flex; align-items:center; gap:6px; font-size:14px; color:#006A3B; text-decoration:none; font-weight:600;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="{{ $isAr ? 'transform:scaleX(-1)' : '' }}"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                {{ $isAr ? 'العودة إلى الأخبار' : 'Back to News' }}
            </a>
        </div>
    </div>

</div>

<style>
.article-content {
    font-size: 16px;
    line-height: 1.85;
    color: #2d2d2d;
    font-family: 'Cairo', sans-serif;
}
.article-content h1 { font-size: 2rem;  font-weight: 800; margin: 1.5rem 0 .75rem; color: #111; }
.article-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.4rem 0 .65rem; color: #111; }
.article-content h3 { font-size: 1.2rem; font-weight: 600; margin: 1.2rem 0 .55rem; color: #333; }
.article-content h4 { font-size: 1rem;   font-weight: 600; margin: 1rem 0 .5rem;    color: #444; }
.article-content p  { margin-bottom: 1rem; }
.article-content ul { list-style: disc;    padding-left: 1.5rem; margin: .8rem 0; }
.article-content ol { list-style: decimal; padding-left: 1.5rem; margin: .8rem 0; }
.article-content li { margin-bottom: .4rem; }
.article-content a  { color: #006A3B; text-decoration: underline; }
.article-content a:hover { color: #004d2a; }
.article-content strong, .article-content b { font-weight: 700; }
.article-content em, .article-content i  { font-style: italic; }
.article-content blockquote {
    border-left: 4px solid #006A3B;
    margin: 1.5rem 0;
    padding: 12px 20px;
    background: rgba(0,106,59,.05);
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: #555;
}
.article-content pre {
    background: #f4f4f4;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    overflow-x: auto;
    font-family: monospace;
    font-size: 14px;
    margin: 1rem 0;
}
.article-content img {
    max-width: 100%;
    border-radius: 12px;
    margin: 1.2rem 0;
}
.article-content video {
    max-width: 100%;
    border-radius: 12px;
    margin: 1.2rem 0;
}
.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    font-size: 14px;
}
.article-content th, .article-content td {
    border: 1px solid #e0e0e0;
    padding: 10px 14px;
    text-align: start;
}
.article-content th { background: #f8f9fa; font-weight: 700; }
[dir="rtl"] .article-content ul,
[dir="rtl"] .article-content ol { padding-left: 0; padding-right: 1.5rem; }
[dir="rtl"] .article-content blockquote { border-left: none; border-right: 4px solid #006A3B; border-radius: 8px 0 0 8px; }
</style>
@endsection
