@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', $isAr ? 'سعّر جدول الكميات مجاناً — كيمتا' : 'Price Your BOQ Free — Qimta')
@section('description', $isAr
    ? 'ارفع جدول الكميات واحصل على تسعيرة فورية مجانية — بدون تسجيل مسبق.'
    : 'Upload your BOQ and get instant AI-powered pricing — no account required.')

@section('nav-cta')
    @auth
        <a href="{{ route('enduser.boqs.create') }}" class="btn-nav-cta">
            {{ $isAr ? 'إنشاء BOQ جديد' : 'New BOQ' }}
        </a>
    @else
        <a href="{{ route('enduser.login') }}" class="btn-nav-cta">
            {{ $isAr ? 'تسجيل الدخول' : 'Sign In' }}
        </a>
    @endauth
@endsection

@section('mobile-cta')
    @guest
        <a href="{{ route('enduser.login') }}" class="btn btn-primary">
            {{ $isAr ? 'تسجيل الدخول' : 'Sign In' }}
        </a>
    @endguest
@endsection

@section('styles')
<style>
/* ── Base ─────────────────────────────────────────────────────────────── */
.try-page {
    background: #f0f4f8;
    font-family: 'Cairo', sans-serif;
    min-height: 100vh;
}

/* ── Hero ─────────────────────────────────────────────────────────────── */
.try-hero {
    padding: 110px 0 90px;
    background: linear-gradient(150deg, #e8fdf3 0%, #f5fffe 45%, #edfaf4 100%);
    position: relative;
    overflow: hidden;
}

.try-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(#a7f3d0 1.2px, transparent 1.2px);
    background-size: 28px 28px;
    opacity: .35;
}

.try-glow-tr,
.try-glow-bl {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.try-glow-tr {
    top: -180px;
    left: -220px;
    width: 640px;
    height: 640px;
    background: radial-gradient(circle, #6ee7b740 0%, transparent 68%);
}

.try-glow-bl {
    bottom: -140px;
    right: -160px;
    width: 520px;
    height: 520px;
    background: radial-gradient(circle, #34d39930 0%, transparent 68%);
}

.try-hero-inner {
    max-width: 880px;
    margin: 0 auto;
    text-align: center;
    padding: 0 32px;
    position: relative;
    z-index: 2;
}

/* ── Badge ────────────────────────────────────────────────────────────── */
.try-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,.9);
    backdrop-filter: blur(8px);
    border: 1.5px solid #6ee7b7;
    color: #059669;
    font-size: 13px;
    font-weight: 800;
    border-radius: 50px;
    padding: 9px 24px;
    margin-bottom: 36px;
    box-shadow: 0 6px 24px #10b98120, 0 1px 0 #fff inset;
    letter-spacing: .3px;
}

.try-badge-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    flex-shrink: 0;
    animation: tbpulse 2.2s ease-in-out infinite;
}

@keyframes tbpulse {
    0%, 100% { transform: scale(1); opacity: 1; box-shadow: 0 0 0 0 #10b98160; }
    50%       { transform: scale(1.35); opacity: .6; box-shadow: 0 0 0 5px #10b98100; }
}

/* ── Heading ──────────────────────────────────────────────────────────── */
.try-hero h1 {
    font-size: clamp(36px, 5.5vw, 72px);
    font-weight: 950;
    line-height: 1.18;
    color: #0a1628;
    margin: 0 0 26px;
    letter-spacing: -1px;
}

.try-highlight {
    color: #059669;
    position: relative;
    display: inline-block;
    white-space: nowrap;
}

.try-highlight::after {
    content: '';
    position: absolute;
    inset-inline-start: 0;
    inset-inline-end: 0;
    bottom: -6px;
    height: 7px;
    background: linear-gradient(90deg, #34d399, #059669);
    border-radius: 4px;
    opacity: .7;
}

/* ── Subtitle ─────────────────────────────────────────────────────────── */
.try-sub {
    font-size: 17.5px;
    color: #4b5f78;
    max-width: 660px;
    margin: 0 auto 48px;
    line-height: 2;
}

/* ── Trust Pills ──────────────────────────────────────────────────────── */
.try-trust {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px 20px;
    margin-top: 4px;
}

.try-trust-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    background: rgba(255,255,255,.75);
    border: 1px solid #e2e8f0;
    border-radius: 30px;
    padding: 6px 16px;
    backdrop-filter: blur(4px);
}

.try-trust-item svg {
    color: #10b981;
    flex-shrink: 0;
}

.try-trust-sep { display: none; }

/* ── Steps Bar ────────────────────────────────────────────────────────── */
.try-steps-wrap {
    background: #fff;
    border-top: 1px solid #dde4ee;
    border-bottom: 1px solid #dde4ee;
    padding: 40px 32px;
    box-shadow: 0 2px 12px rgba(15,23,42,.05);
}

.try-steps {
    max-width: 980px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 8px;
}

.try-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 14px;
    padding: 4px 8px;
}

.try-step-num {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border: 2px solid #6ee7b7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 900;
    color: #059669;
    box-shadow: 0 4px 12px #10b98120;
    flex-shrink: 0;
}

.try-step-label {
    font-size: 13.5px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.5;
}

.try-step-sublabel {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 2px;
    font-weight: 600;
}

.try-step-arrow {
    color: #cbd5e1;
    font-size: 22px;
    margin-bottom: 38px;
    flex-shrink: 0;
}

/* ── Wizard ───────────────────────────────────────────────────────────── */
.try-wizard-wrap {
    max-width: 1080px;
    margin: 0 auto;
    padding: 56px 28px 96px;
}

.try-wizard-card {
    background: #fff;
    border: 1px solid #dde4ee;
    border-radius: 28px;
    box-shadow:
        0 4px 6px rgba(15,23,42,.04),
        0 16px 48px rgba(15,23,42,.09),
        0 0 0 1px rgba(255,255,255,.6) inset;
    overflow: hidden;
}

.try-wizard-inner {
    padding: 36px 40px 40px;
}

@media (max-width: 768px) {
    .try-wizard-inner {
        padding: 24px 18px 32px;
    }
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .try-hero {
        padding: 72px 0 60px;
    }
    .try-hero h1 {
        letter-spacing: -.5px;
    }
    .try-sub {
        font-size: 15.5px;
        margin-bottom: 36px;
    }
    .try-steps-wrap {
        padding: 28px 20px;
    }
    .try-steps {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .try-step {
        flex-direction: row;
        text-align: start;
        justify-content: flex-start;
        align-items: center;
    }
    .try-step-arrow {
        display: none;
    }
    .try-wizard-wrap {
        padding: 28px 14px 64px;
    }
    .try-wizard-card {
        border-radius: 20px;
    }
}
</style>
@endsection

@section('content')

<div class="try-page">

    <section class="try-hero" @if($isAr) dir="rtl" @endif>
        <div class="try-glow-tr"></div>
        <div class="try-glow-bl"></div>

        <div class="try-hero-inner">
            <div class="try-badge">
                <span class="try-badge-dot"></span>
                {{ $isAr ? 'مجاناً — بدون تسجيل حساب' : 'Free — No account required' }}
            </div>

            <h1>
                @if($isAr)
                    سعّر <span class="try-highlight">جدول الكميات</span><br>في ثوانٍ معدودة
                @else
                    Price Your <span class="try-highlight">BOQ</span><br>in Seconds
                @endif
            </h1>

            <p class="try-sub">
                {{ $isAr
                    ? 'ارفع ملف جدول الكميات وسيقوم الذكاء الاصطناعي بتحليله وإحضار الأسعار تلقائياً — من أكثر من 131,000 منتج موثّق في السوق السعودي.'
                    : 'Upload any BOQ file and our AI extracts line items and fetches live market prices automatically — across 131,000+ verified products.' }}
            </p>

            <div class="try-trust">
                <span class="try-trust-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $isAr ? '131,000+ منتج موثّق' : '131,000+ verified products' }}
                </span>

                <span class="try-trust-sep">·</span>

                <span class="try-trust-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                    </svg>
                    {{ $isAr ? 'نتيجة في أقل من 60 ثانية' : 'Results in under 60 seconds' }}
                </span>

                <span class="try-trust-sep">·</span>

                <span class="try-trust-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    {{ $isAr ? 'آمن وسري تماماً' : 'Secure & private' }}
                </span>

                <span class="try-trust-sep">·</span>

                <span class="try-trust-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.87L12 17.77l-6.18 3.24L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    {{ $isAr ? 'مجاني للمشترين دائماً' : 'Free for buyers forever' }}
                </span>
            </div>
        </div>
    </section>

    <div class="try-steps-wrap" @if($isAr) dir="rtl" @endif>
        <div class="try-steps">
            @php
                $steps = $isAr
                    ? [
                        ['١', 'ارفع ملف BOQ', 'Excel أو PDF أو صورة'],
                        ['٢', 'AI يستخرج البنود', 'تلقائياً بدون تدخل'],
                        ['٣', 'تسعير فوري من السوق', 'أسعار حقيقية ومحدّثة'],
                        ['٤', 'سجّل دخول وحمّل PDF', 'احفظ وشارك عرض السعر'],
                    ]
                    : [
                        ['1', 'Upload BOQ file', 'Excel, PDF or image'],
                        ['2', 'AI extracts items', 'Automatic, zero effort'],
                        ['3', 'Live market pricing', 'Real, up-to-date prices'],
                        ['4', 'Sign in & download PDF', 'Save & share your quote'],
                    ];
            @endphp

            @foreach($steps as $index => $step)
                <div class="try-step">
                    <div class="try-step-num">{{ $step[0] }}</div>
                    <div>
                        <div class="try-step-label">{{ $step[1] }}</div>
                        <div class="try-step-sublabel">{{ $step[2] }}</div>
                    </div>
                </div>

                @if($index < count($steps) - 1)
                    <div class="try-step-arrow" @if($isAr) style="transform:scaleX(-1)" @endif>→</div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="try-wizard-wrap" @if($isAr) dir="rtl" @endif>
        <div class="try-wizard-card" @if($isAr) dir="rtl" @endif>
            <div class="try-wizard-inner">
                <livewire:enduser.boqs.create-boq :guestMode="true" :guestToken="$guestToken" />
            </div>
        </div>
    </div>

</div>

@endsection
