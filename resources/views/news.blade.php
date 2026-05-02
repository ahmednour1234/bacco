@extends('layouts.app')

@php
    $isAr   = app()->getLocale() === 'ar';
    $dir    = $isAr ? 'rtl' : 'ltr';

    // Helper: strip HTML and compute read time (words / 200 wpm)
    function readTime(string $html): int {
        $words = str_word_count(strip_tags($html));
        return max(1, (int) ceil($words / 200));
    }
@endphp

@section('title', $isAr ? 'الأخبار والمقالات' : 'News & Insights')

@section('content')
<div style="background:#f8f9fa; min-height:100vh;" dir="{{ $dir }}">

    {{-- ── Breadcrumb ──────────────────────────────────────────────────── --}}
    <div style="background:#fff; border-bottom:1px solid #eee; padding:10px 0;">
        <div style="max-width:1200px; margin:0 auto; padding:0 24px;">
            <nav style="font-size:13px; color:#888; display:flex; align-items:center; gap:6px;">
                <a href="{{ url('/') }}" style="color:#888; text-decoration:none;">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="{{ $isAr ? 'transform:scaleX(-1)' : '' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span style="color:#333;">{{ $isAr ? 'الأخبار' : 'News' }}</span>
            </nav>
        </div>
    </div>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div style="background:#fff; padding:48px 0 32px;">
        <div style="max-width:1200px; margin:0 auto; padding:0 24px;">
            <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:24px;">
                <div>
                    <h1 style="font-size:clamp(1.8rem,4vw,2.6rem); font-weight:800; color:#111; margin:0 0 4px; font-family:'Cairo',sans-serif; line-height:1.15;">
                        {{ $isAr ? 'الأخبار والمقالات' : 'News & Insights' }}
                    </h1>
                    <p style="font-size:14px; color:#006A3B; font-family:'Cairo',sans-serif; margin:0 0 12px; font-weight:600;">
                        {{ $isAr ? 'News & Insights' : 'الأخبار والمقالات' }}
                    </p>
                    <p style="font-size:14px; color:#666; max-width:520px; line-height:1.7; margin:0;">
                        {{ $isAr
                            ? 'تحليل الصناعة، شرح المنتجات، تحديثات المعايير، فيديوهات تعليمية، وأدلة تسعير مشاريع البناء.'
                            : 'Industry analysis, product explainers, standards updates, video walkthroughs, and construction pricing guides.' }}
                    </p>
                </div>

                {{-- Search --}}
                <form method="GET" action="{{ route('news') }}" style="display:flex; align-items:center;">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div style="position:relative; width:280px;">
                        <svg style="position:absolute; {{ $isAr ? 'left' : 'right' }}:12px; top:50%; transform:translateY(-50%); color:#aaa;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="search" name="search" value="{{ $search }}"
                               placeholder="{{ $isAr ? 'ابحث في المقالات…' : 'Search articles, videos, guides…' }}"
                               style="width:100%; padding:10px 40px 10px 14px; border:1px solid #e0e0e0; border-radius:50px; font-size:13px; outline:none; background:#f8f9fa; box-sizing:border-box; {{ $isAr ? 'padding-left:40px;padding-right:14px;' : '' }}">
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Filter Tabs ─────────────────────────────────────────────────── --}}
    <div style="background:#fff; border-bottom:1px solid #eee; padding-bottom:0;">
        <div style="max-width:1200px; margin:0 auto; padding:0 24px;">
            <div style="display:flex; flex-wrap:wrap; gap:8px; padding:16px 0;">
                {{-- All --}}
                <a href="{{ route('news', array_filter(['search' => $search])) }}"
                   style="padding:7px 18px; border-radius:50px; font-size:13px; font-weight:600; text-decoration:none; transition:all .15s;
                          {{ !$category ? 'background:#006A3B; color:#fff;' : 'background:#f0f0f0; color:#555;' }}">
                    {{ $isAr ? 'كل المحتوى' : 'All Insights' }}
                </a>

                @foreach($categories as $nameAr => $nameEn)
                    <a href="{{ route('news', array_filter(['category' => $nameEn, 'search' => $search])) }}"
                       style="padding:7px 18px; border-radius:50px; font-size:13px; font-weight:600; text-decoration:none; transition:all .15s;
                              {{ $category === $nameEn ? 'background:#006A3B; color:#fff;' : 'background:#f0f0f0; color:#555;' }}">
                        {{ $isAr ? $nameAr : $nameEn }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Main content ────────────────────────────────────────────────── --}}
    <div style="max-width:1200px; margin:0 auto; padding:40px 24px;">

        @if(!$featured && $rest->isEmpty())
            <div style="text-align:center; padding:80px 0; color:#999;">
                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px; display:block; color:#ccc;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v10a2 2 0 01-2 2zM12 11v6M9 14h6"/></svg>
                <p style="font-size:16px;">{{ $isAr ? 'لا توجد مقالات.' : 'No articles found.' }}</p>
            </div>
        @else

            {{-- ── Featured hero card ──────────────────────────────────── --}}
            @if($featured)
            <a href="{{ route('news.show', $featured->uuid) }}" style="display:block; text-decoration:none; margin-bottom:40px;">
                <div style="border-radius:16px; overflow:hidden; background:#111; min-height:340px; display:grid; grid-template-columns:55% 1fr; position:relative; box-shadow:0 4px 30px rgba(0,0,0,.13); transition:transform .2s; cursor:pointer;"
                     onmouseenter="this.style.transform='translateY(-3px)'"
                     onmouseleave="this.style.transform='translateY(0)'">

                    {{-- Image side --}}
                    <div style="position:relative; min-height:340px; background:#1a1a2e;">
                        @if($featured->image)
                            <img src="{{ Storage::url($featured->image) }}" alt=""
                                 style="width:100%; height:100%; object-fit:cover; opacity:.75; display:block;">
                        @else
                            {{-- Placeholder gradient --}}
                            <div style="width:100%; height:100%; background:linear-gradient(135deg,#0d1b2a 0%,#1b263b 50%,#0a1628 100%); display:flex; align-items:center; justify-content:center;">
                                <svg width="80" height="80" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="1" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="40"/><circle cx="50" cy="50" r="28"/><circle cx="50" cy="50" r="16"/>
                                    <line x1="50" y1="10" x2="50" y2="0"/><line x1="50" y1="90" x2="50" y2="100"/>
                                    <line x1="10" y1="50" x2="0" y2="50"/><line x1="90" y1="50" x2="100" y2="50"/>
                                </svg>
                            </div>
                        @endif
                        <div style="position:absolute; inset:0; background:linear-gradient(to {{ $isAr ? 'left' : 'right' }}, transparent 60%, #111 100%);"></div>
                    </div>

                    {{-- Text side --}}
                    <div style="padding:36px 36px; display:flex; flex-direction:column; justify-content:center; background:#111;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                            <span style="background:#006A3B; color:#fff; font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:4px 10px; border-radius:4px;">
                                {{ $isAr ? ($featured->name_ar ?? $featured->name_en) : $featured->name_en }}
                            </span>
                            <span style="background:rgba(255,255,255,.1); color:#aaa; font-size:10px; font-weight:600; padding:4px 10px; border-radius:4px; letter-spacing:.05em;">
                                EN | AR
                            </span>
                        </div>
                        <h2 style="font-size:clamp(1.2rem,2.5vw,1.75rem); font-weight:800; color:#fff; margin:0 0 14px; line-height:1.25; font-family:'Cairo',sans-serif;">
                            {{ $isAr ? $featured->title_ar : $featured->title_en }}
                        </h2>
                        <p style="font-size:13px; color:#999; line-height:1.7; margin:0 0 20px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                            {{ Str::limit(strip_tags($isAr ? $featured->desc_ar : $featured->desc_en), 180) }}
                        </p>
                        <div style="display:flex; align-items:center; gap:16px; font-size:12px; color:#666;">
                            <span style="display:flex; align-items:center; gap:4px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $featured->created_at->format('M d, Y') }}
                            </span>
                            <span style="display:flex; align-items:center; gap:4px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                @php $rt = readTime(($isAr ? $featured->desc_ar : $featured->desc_en) ?? ''); @endphp
                                {{ $rt }} {{ $isAr ? 'دقيقة قراءة' : 'min read' }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            @endif

            {{-- ── Grid ────────────────────────────────────────────────── --}}
            @if($rest->count())
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:24px; margin-bottom:40px;">
                @foreach($rest as $article)
                <a href="{{ route('news.show', $article->uuid) }}"
                   style="display:block; text-decoration:none; border-radius:12px; background:#fff; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.07); border:1px solid #efefef; transition:transform .2s, box-shadow .2s;"
                   onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'"
                   onmouseleave="this.style.transform='translateY(0)';this.style.boxShadow='0 1px 4px rgba(0,0,0,.07)'">

                    {{-- Thumbnail --}}
                    <div style="position:relative; height:175px; background:#1a1a2e; overflow:hidden;">
                        @if($article->image)
                            <img src="{{ Storage::url($article->image) }}" alt=""
                                 style="width:100%; height:100%; object-fit:cover; display:block;">
                        @else
                            <div style="width:100%; height:100%; background:linear-gradient(135deg,#0d1b2a,#1b263b); display:flex; align-items:center; justify-content:center;">
                                <svg width="40" height="40" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        {{-- Badges overlay --}}
                        <div style="position:absolute; bottom:8px; {{ $isAr ? 'right' : 'left' }}:8px; display:flex; gap:6px;">
                            <span style="background:rgba(0,106,59,.9); color:#fff; font-size:9px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; padding:3px 8px; border-radius:3px; backdrop-filter:blur(4px);">
                                {{ $isAr ? ($article->name_ar ?? $article->name_en) : $article->name_en }}
                            </span>
                            <span style="background:rgba(0,0,0,.5); color:#ccc; font-size:9px; font-weight:600; padding:3px 8px; border-radius:3px; backdrop-filter:blur(4px); letter-spacing:.04em;">
                                EN | AR
                            </span>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div style="padding:16px;">
                        <h3 style="font-size:14px; font-weight:700; color:#111; margin:0 0 10px; line-height:1.4; font-family:'Cairo',sans-serif; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                            {{ $isAr ? $article->title_ar : $article->title_en }}
                        </h3>
                        <div style="display:flex; align-items:center; justify-content:space-between; font-size:11px; color:#999;">
                            <span>{{ $article->created_at->format('M d, Y') }}</span>
                            <span>
                                @php $rt2 = readTime(($isAr ? $article->desc_ar : $article->desc_en) ?? ''); @endphp
                                {{ $rt2 }} {{ $isAr ? 'دقيقة' : 'min read' }}
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Load More / Pagination --}}
            @if($rest->hasPages())
            <div style="text-align:center; padding:12px 0 48px;">
                @if($rest->hasMorePages())
                    <a href="{{ $rest->nextPageUrl() }}"
                       style="display:inline-flex; align-items:center; gap:8px; padding:12px 32px; border:1.5px solid #006A3B; border-radius:50px; color:#006A3B; font-size:14px; font-weight:600; text-decoration:none; transition:all .15s;"
                       onmouseenter="this.style.background='#006A3B';this.style.color='#fff'"
                       onmouseleave="this.style.background='transparent';this.style.color='#006A3B'">
                        {{ $isAr ? 'تحميل المزيد من المقالات' : 'Load More Articles' }}
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                    </a>
                @endif
            </div>
            @endif

            @endif
        @endif
    </div>

    {{-- ── CTA ──────────────────────────────────────────────────────────── --}}
    <div style="background:#111; padding:80px 24px; text-align:center;">
        <div style="max-width:600px; margin:0 auto;">
            <h2 style="font-size:clamp(1.5rem,3vw,2.2rem); font-weight:800; color:#fff; margin:0 0 12px; font-family:'Cairo',sans-serif;">
                {{ $isAr ? 'جرّب محرك التسعير' : 'Try the pricing engine' }}
            </h2>
            <p style="font-size:15px; color:#999; margin:0 0 32px; line-height:1.7;">
                {{ $isAr
                    ? 'ارفع جدول الكميات وتلقّ عروض أسعار حقيقية خلال دقائق من موردين معتمدين.'
                    : 'Upload your BOQ and get real supplier quotes in minutes — powered by Qimta.' }}
            </p>
            <a href="{{ url('/') }}"
               style="display:inline-flex; align-items:center; gap:8px; background:#006A3B; color:#fff; padding:14px 36px; border-radius:50px; font-size:15px; font-weight:700; text-decoration:none; transition:background .15s;"
               onmouseenter="this.style.background='#005a31'"
               onmouseleave="this.style.background='#006A3B'">
                {{ $isAr ? 'ابدأ الآن' : 'Get Started' }}
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="{{ $isAr ? 'transform:scaleX(-1)' : '' }}"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

</div>
@endsection
