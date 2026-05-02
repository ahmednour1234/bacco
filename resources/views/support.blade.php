@extends('layouts.app')

@section('title', __('support.hero.h1'))

@section('content')
@php $isAr = app()->getLocale() === 'ar'; @endphp

<style>
/* ── BASE ────────────────────────────────────────────────────────────────── */
.sup-page { font-family: 'Cairo','Inter',sans-serif; color: var(--dark); background: var(--cream); }

/* ── HERO ────────────────────────────────────────────────────────────────── */
.sup-hero {
    background: var(--cream);
    padding: 80px 24px 0;
    text-align: center;
}
.sup-hero-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--green);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    margin-bottom: 20px;
}
.sup-hero h1 {
    font-size: clamp(26px, 3.5vw, 38px);
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--dark);
}
.sup-hero-ar {
    font-size: clamp(18px, 2.5vw, 26px);
    color: var(--green);
    font-weight: 700;
    margin: 0 0 20px;
    display: block;
}
.sup-hero-sub {
    font-size: 14.5px;
    color: var(--gray);
    line-height: 1.75;
    max-width: 560px;
    margin: 0 auto 36px;
}
.sup-search-wrap {
    max-width: 580px;
    margin: 0 auto 0;
    position: relative;
}
.sup-search-wrap svg {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #aaa;
    pointer-events: none;
}
[dir=rtl] .sup-search-wrap svg { left: auto; right: 16px; }
.sup-search {
    width: 100%;
    padding: 13px 16px 13px 44px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Cairo','Inter',sans-serif;
    color: var(--dark);
    background: #fff;
    outline: none;
    box-sizing: border-box;
    transition: border-color .2s;
}
[dir=rtl] .sup-search { padding: 13px 44px 13px 16px; }
.sup-search:focus { border-color: var(--green); }

/* ── TABS ────────────────────────────────────────────────────────────────── */
.sup-tabs-wrap {
    background: var(--cream);
    padding: 0 24px;
    position: sticky;
    top: 64px;
    z-index: 10;
}
.sup-tabs {
    max-width: 760px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0;
    border-bottom: 1px solid var(--border);
    padding-top: 24px;
}
.sup-tab {
    background: none;
    border: none;
    border-bottom: 2.5px solid transparent;
    margin-bottom: -1px;
    padding: 10px 16px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--gray);
    cursor: pointer;
    font-family: 'Cairo','Inter',sans-serif;
    transition: color .2s, border-color .2s;
    white-space: nowrap;
}
.sup-tab:hover { color: var(--dark); }
.sup-tab.active { color: var(--dark); border-bottom-color: var(--dark); }

/* ── CONTENT ─────────────────────────────────────────────────────────────── */
.sup-content {
    max-width: 760px;
    margin: 0 auto;
    padding: 56px 24px 0;
}

/* ── SECTION ─────────────────────────────────────────────────────────────── */
.sup-section { margin-bottom: 56px; }
.sup-section-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--dark);
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sup-section-title::before {
    content: '';
    display: block;
    width: 4px;
    height: 18px;
    background: var(--green);
    border-radius: 2px;
    flex-shrink: 0;
}

/* ── FAQ ITEMS ───────────────────────────────────────────────────────────── */
.faq-list { display: flex; flex-direction: column; gap: 8px; }
.faq-item {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow .2s;
}
.faq-item:hover { box-shadow: 0 2px 12px rgba(0,0,0,.06); }
.faq-q {
    width: 100%;
    background: none;
    border: none;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 20px;
    font-size: 14px;
    font-weight: 600;
    color: var(--dark);
    cursor: pointer;
    font-family: 'Cairo','Inter',sans-serif;
    gap: 16px;
}
[dir=rtl] .faq-q { text-align: right; }
.faq-q svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    color: var(--green);
    transition: transform .25s;
}
.faq-item.open .faq-q svg { transform: rotate(180deg); }
.faq-a {
    display: none;
    padding: 2px 20px 18px;
    font-size: 13.5px;
    color: var(--gray);
    line-height: 1.8;
}
.faq-item.open .faq-a { display: block; }

/* ── RAG FEATURED CARD ───────────────────────────────────────────────────── */
.rag-card {
    background: rgba(0,106,59,.06);
    border: 1px solid rgba(0,106,59,.18);
    border-radius: 10px;
    padding: 24px 24px 22px;
    margin-bottom: 16px;
}
.rag-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--green);
    margin: 0 0 12px;
}
.rag-card-title svg { width: 16px; height: 16px; flex-shrink: 0; }
.rag-card p {
    font-size: 13.5px;
    color: var(--gray);
    line-height: 1.8;
    margin: 0;
}

/* ── SECURITY COMPLIANCE CARDS ───────────────────────────────────────────── */
.sec-compliance-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
}
.sec-comp-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px 18px;
}
.sec-comp-card h4 { font-size: 14px; font-weight: 700; margin: 0 0 8px; color: var(--dark); }
.sec-comp-card p  { font-size: 13px; color: var(--gray); line-height: 1.65; margin: 0; }

/* ── NO RESULTS ──────────────────────────────────────────────────────────── */
.sup-no-results {
    text-align: center;
    padding: 48px 0;
    color: var(--gray);
    font-size: 14px;
    display: none;
}

/* ── CTA ─────────────────────────────────────────────────────────────────── */
.sup-cta {
    background: #edecea;
    border-top: 1px solid var(--border);
    padding: 64px 24px;
    text-align: center;
    margin-top: 40px;
}
.sup-cta h2 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 12px;
    color: var(--dark);
}
.sup-cta p {
    font-size: 14px;
    color: var(--gray);
    line-height: 1.7;
    margin: 0 auto 28px;
    max-width: 480px;
}
.sup-cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.sup-btn-primary {
    background: var(--green);
    color: #fff;
    border: none;
    padding: 13px 28px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    font-family: 'Cairo','Inter',sans-serif;
    transition: background .2s;
}
.sup-btn-primary:hover { background: #005530; }
.sup-btn-outline {
    background: transparent;
    color: var(--dark);
    border: 2px solid var(--border);
    padding: 12px 28px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    font-family: 'Cairo','Inter',sans-serif;
    transition: border-color .2s, color .2s;
}
.sup-btn-outline:hover { border-color: var(--dark); }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 600px) {
    .sec-compliance-grid { grid-template-columns: 1fr; }
    .sup-tab { padding: 10px 10px; font-size: 11px; }
}
</style>

<div class="sup-page">

    {{-- ── HERO ──────────────────────────────────────────────────────────── --}}
    <div class="sup-hero">
        <div class="sup-hero-label">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            {{ __('support.hero.label') }}
        </div>
        <h1>{{ __('support.hero.h1') }}</h1>
        <span class="sup-hero-ar">{{ __('support.hero.h1_ar') }}</span>
        <p class="sup-hero-sub">{{ __('support.hero.sub') }}</p>
        <div class="sup-search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                type="text"
                class="sup-search"
                id="supSearch"
                placeholder="{{ __('support.hero.search') }}"
                autocomplete="off"
            >
        </div>
    </div>

    {{-- ── TABS ──────────────────────────────────────────────────────────── --}}
    <div class="sup-tabs-wrap">
        <div class="sup-tabs" id="supTabs">
            @foreach(['pricing','catalog','boq','rag','ordering','brands','security','account'] as $tab)
            <button class="sup-tab{{ $loop->first ? ' active' : '' }}"
                    data-tab="{{ $tab }}"
                    onclick="scrollToSection('{{ $tab }}', this)">
                {{ __("support.tabs.{$tab}") }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ── FAQ CONTENT ───────────────────────────────────────────────────── --}}
    <div class="sup-content" id="supContent">

        <p class="sup-no-results" id="supNoResults">No matching questions found.</p>

        {{-- PRICING --}}
        <div class="sup-section" id="sec-pricing" data-section="pricing">
            <div class="sup-section-title">{{ __('support.pricing.title') }}</div>
            <div class="faq-list">
                @foreach(['1','2','3'] as $i)
                <div class="faq-item{{ $i==='1' ? ' open' : '' }}" data-text="{{ strtolower(__('support.pricing.q'.$i).' '.__('support.pricing.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.pricing.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.pricing.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CATALOG --}}
        <div class="sup-section" id="sec-catalog" data-section="catalog">
            <div class="sup-section-title">{{ __('support.catalog.title') }}</div>
            <div class="faq-list">
                @foreach(['1','2','3'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.catalog.q'.$i).' '.__('support.catalog.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.catalog.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.catalog.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- BOQ --}}
        <div class="sup-section" id="sec-boq" data-section="boq">
            <div class="sup-section-title">{{ __('support.boq.title') }}</div>
            <div class="faq-list">
                @foreach(['1','2','3'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.boq.q'.$i).' '.__('support.boq.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.boq.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.boq.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- RAG --}}
        <div class="sup-section" id="sec-rag" data-section="rag">
            <div class="sup-section-title">{{ __('support.rag.title') }}</div>
            <div class="rag-card">
                <div class="rag-card-title">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    {{ __('support.rag.card_title') }}
                </div>
                <p>{{ __('support.rag.card_body') }}</p>
            </div>
            <div class="faq-list">
                @foreach(['1','2'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.rag.q'.$i).' '.__('support.rag.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.rag.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.rag.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ORDERING --}}
        <div class="sup-section" id="sec-ordering" data-section="ordering">
            <div class="sup-section-title">{{ __('support.ordering.title') }}</div>
            <div class="faq-list">
                @foreach(['1','2'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.ordering.q'.$i).' '.__('support.ordering.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.ordering.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.ordering.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- BRANDS --}}
        <div class="sup-section" id="sec-brands" data-section="brands">
            <div class="sup-section-title">{{ __('support.brands.title') }}</div>
            <div class="faq-list">
                @foreach(['1','2'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.brands.q'.$i).' '.__('support.brands.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.brands.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.brands.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- SECURITY --}}
        <div class="sup-section" id="sec-security" data-section="security">
            <div class="sup-section-title">{{ __('support.security.title') }}</div>
            <div class="sec-compliance-grid">
                <div class="sec-comp-card">
                    <h4>{{ __('support.security.card1_title') }}</h4>
                    <p>{{ __('support.security.card1_body') }}</p>
                </div>
                <div class="sec-comp-card">
                    <h4>{{ __('support.security.card2_title') }}</h4>
                    <p>{{ __('support.security.card2_body') }}</p>
                </div>
            </div>
            <div class="faq-list">
                @foreach(['1','2'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.security.q'.$i).' '.__('support.security.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.security.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.security.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ACCOUNT --}}
        <div class="sup-section" id="sec-account" data-section="account">
            <div class="sup-section-title">{{ __('support.account.title') }}</div>
            <div class="faq-list">
                @foreach(['1','2'] as $i)
                <div class="faq-item" data-text="{{ strtolower(__('support.account.q'.$i).' '.__('support.account.a'.$i)) }}">
                    <button class="faq-q" onclick="toggleFaq(this)">
                        {{ __("support.account.q{$i}") }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-a">{{ __("support.account.a{$i}") }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /sup-content --}}

    {{-- ── CTA ─────────────────────────────────────────────────────────────--}}
    <div class="sup-cta">
        <h2>{{ __('support.cta.title') }}</h2>
        <p>{{ __('support.cta.sub') }}</p>
        <div class="sup-cta-btns">
            <a href="#" class="sup-btn-primary">{{ __('support.cta.btn1') }}</a>
            <a href="{{ route('contact') }}" class="sup-btn-outline">{{ __('support.cta.btn2') }}</a>
        </div>
    </div>

</div>

<script>
// ── FAQ toggle ───────────────────────────────────────────────────────────────
function toggleFaq(btn) {
    var item = btn.closest('.faq-item');
    var isOpen = item.classList.contains('open');
    // close siblings in same list
    var siblings = item.closest('.faq-list').querySelectorAll('.faq-item');
    siblings.forEach(function(el) { el.classList.remove('open'); });
    if (!isOpen) item.classList.add('open');
}

// ── Tab scroll ───────────────────────────────────────────────────────────────
function scrollToSection(tab, btnEl) {
    var target = document.getElementById('sec-' + tab);
    if (!target) return;
    var offset = 130; // nav + sticky tabs
    var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top: top, behavior: 'smooth' });
    document.querySelectorAll('.sup-tab').forEach(function(b) { b.classList.remove('active'); });
    btnEl.classList.add('active');
}

// ── Active tab on scroll ────────────────────────────────────────────────────
(function() {
    var sections = document.querySelectorAll('.sup-section[data-section]');
    var tabs = document.querySelectorAll('.sup-tab');
    var searching = false;

    window.addEventListener('scroll', function() {
        if (searching) return;
        var scrollY = window.pageYOffset + 160;
        var active = null;
        sections.forEach(function(sec) {
            if (sec.offsetTop <= scrollY) active = sec.dataset.section;
        });
        if (active) {
            tabs.forEach(function(t) {
                t.classList.toggle('active', t.dataset.tab === active);
            });
        }
    });

    // ── Search filter ─────────────────────────────────────────────────────
    var searchInput = document.getElementById('supSearch');
    var noResults   = document.getElementById('supNoResults');

    searchInput.addEventListener('input', function() {
        var q = this.value.trim().toLowerCase();
        searching = q.length > 0;

        var anyVisible = false;
        sections.forEach(function(sec) {
            var items   = sec.querySelectorAll('.faq-item');
            var secShow = false;
            items.forEach(function(item) {
                var match = !q || (item.dataset.text || '').indexOf(q) !== -1;
                item.style.display = match ? '' : 'none';
                if (match) secShow = true;
            });
            // also match section title
            var titleMatch = !q || sec.querySelector('.sup-section-title').textContent.toLowerCase().indexOf(q) !== -1;
            if (titleMatch && q) {
                items.forEach(function(item) { item.style.display = ''; });
                secShow = true;
            }
            sec.style.display = (!q || secShow) ? '' : 'none';
            if (!q || secShow) anyVisible = true;
        });

        noResults.style.display = (!anyVisible && q) ? 'block' : 'none';

        if (!q) {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            if (tabs[0]) tabs[0].classList.add('active');
        }
    });
})();
</script>
@endsection
