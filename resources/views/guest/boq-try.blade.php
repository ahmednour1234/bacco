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
.try-page {
    background: #f8fafc;
    font-family: 'Cairo', sans-serif;
}

.try-hero {
    padding: 90px 0 76px;
    background: linear-gradient(155deg, #f0fdf8 0%, #f8fdf9 55%, #ecfdf5 100%);
    position: relative;
    overflow: hidden;
}

.try-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(#bbf7d0 1.3px, transparent 1.3px);
    background-size: 30px 30px;
    opacity: .4;
}

.try-glow-tr,
.try-glow-bl {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.try-glow-tr {
    top: -160px;
    left: -200px;
    width: 580px;
    height: 580px;
    background: radial-gradient(circle, #6ee7b730 0%, transparent 68%);
}

.try-glow-bl {
    bottom: -120px;
    right: -150px;
    width: 460px;
    height: 460px;
    background: radial-gradient(circle, #a7f3d020 0%, transparent 68%);
}

.try-hero-inner {
    max-width: 860px;
    margin: 0 auto;
    text-align: center;
    padding: 0 28px;
    position: relative;
    z-index: 2;
}

.try-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 1.5px solid #6ee7b7;
    color: #059669;
    font-size: 13.5px;
    font-weight: 800;
    border-radius: 50px;
    padding: 8px 22px;
    margin-bottom: 32px;
    box-shadow: 0 8px 24px #10b98117;
}

.try-badge-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: tbpulse 2.2s ease-in-out infinite;
}

@keyframes tbpulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: .5; }
}

.try-hero h1 {
    font-size: clamp(38px, 6vw, 74px);
    font-weight: 950;
    line-height: 1.15;
    color: #0f172a;
    margin: 0 0 24px;
}

.try-highlight {
    color: #059669;
    position: relative;
    display: inline-block;
}

.try-highlight::after {
    content: '';
    position: absolute;
    inset-inline-start: 2px;
    inset-inline-end: 2px;
    bottom: -4px;
    height: 6px;
    background: linear-gradient(90deg, #34d399, #059669);
    border-radius: 4px;
    opacity: .75;
}

.try-sub {
    font-size: 18px;
    color: #475569;
    max-width: 640px;
    margin: 0 auto 42px;
    line-height: 1.9;
}

.try-trust {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 14px 24px;
}

.try-trust-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 13.5px;
    font-weight: 700;
    color: #64748b;
}

.try-trust-item svg {
    color: #10b981;
    flex-shrink: 0;
}

.try-trust-sep {
    color: #cbd5e1;
}

.try-steps-wrap {
    background: #fff;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    padding: 36px 28px;
}

.try-steps {
    max-width: 960px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 12px;
}

.try-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
}

.try-step-num {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #ecfdf5;
    border: 2px solid #a7f3d0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    font-weight: 900;
    color: #059669;
}

.try-step-label {
    font-size: 13.5px;
    font-weight: 800;
    color: #334155;
    line-height: 1.5;
}

.try-step-sublabel {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 3px;
}

.try-step-arrow {
    color: #cbd5e1;
    font-size: 24px;
    margin-bottom: 34px;
}

.try-wizard-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 48px 24px 80px;
}

.try-wizard-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    box-shadow: 0 8px 40px rgba(15, 23, 42, .08);
    padding: 0;
    overflow: hidden;
}

@media (max-width: 768px) {
    .try-hero {
        padding: 64px 0 54px;
    }

    .try-sub {
        font-size: 16px;
    }

    .try-trust-sep {
        display: none;
    }

    .try-steps {
        grid-template-columns: 1fr;
        gap: 20px;
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
        padding: 24px 12px 60px;
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
            <livewire:enduser.boqs.create-boq :guestMode="true" :guestToken="$guestToken" />
        </div>
    </div>

</div>

@endsection
