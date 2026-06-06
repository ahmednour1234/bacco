@extends('layouts.app')

@php $isAr = true; /* forced Arabic on /try */ @endphp

@section('title', 'سعّر جدول الكميات مجاناً — كيمتا')
@section('description', 'ارفع جدول الكميات واحصل على تسعيرة فورية مجانية — بدون تسجيل مسبق.')

@section('no_ar_hreflang', true)

@section('nav-cta')
    @auth
        <a href="{{ route('enduser.boqs.create') }}" class="btn-nav-cta">
            إنشاء BOQ جديد
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    @else
        <a href="{{ route('enduser.login') }}" class="btn-nav-cta">تسجيل الدخول</a>
    @endauth
@endsection

@section('mobile-cta')
    @guest
        <a href="{{ route('enduser.login') }}" class="btn btn-primary">تسجيل الدخول</a>
    @endguest
@endsection

@section('styles')
<style>
/* ── Force RTL ── */
body { direction: rtl; text-align: right; }

/* ──────────────────────────────────
   HERO
────────────────────────────────── */
.try-hero {
    padding: 90px 0 76px;
    background: linear-gradient(155deg,#f0fdf8 0%,#f8fdf9 55%,#ecfdf5 100%);
    position: relative;
    overflow: hidden;
}
.try-hero::before {
    content:'';position:absolute;inset:0;
    background-image:radial-gradient(#bbf7d0 1.3px,transparent 1.3px);
    background-size:30px 30px;opacity:.4;pointer-events:none;
}
.try-glow-tr {
    position:absolute;top:-160px;left:-200px;
    width:580px;height:580px;
    background:radial-gradient(circle,#6ee7b730 0%,transparent 68%);
    pointer-events:none;
}
.try-glow-bl {
    position:absolute;bottom:-120px;right:-150px;
    width:460px;height:460px;
    background:radial-gradient(circle,#a7f3d020 0%,transparent 68%);
    pointer-events:none;
}
.try-hero-inner {
    max-width:860px;margin:0 auto;
    text-align:center;padding:0 28px;
    position:relative;z-index:1;
}
.try-badge {
    display:inline-flex;align-items:center;gap:10px;
    background:#fff;border:1.5px solid #6ee7b7;color:#059669;
    font-size:13.5px;font-weight:700;border-radius:50px;
    padding:7px 22px;margin-bottom:32px;
    box-shadow:0 2px 14px #10b98118;font-family:'Cairo',sans-serif;
}
.try-badge-dot {
    width:8px;height:8px;background:#10b981;border-radius:50%;
    animation:tbpulse 2.2s ease-in-out infinite;flex-shrink:0;
}
@keyframes tbpulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.5}}
.try-hero h1 {
    font-family:'Cairo',sans-serif;
    font-size:clamp(44px,6vw,78px);
    font-weight:950;line-height:1.15;letter-spacing:0;
    color:#0f172a;margin-bottom:24px;
}
.try-highlight {
    color:#059669;position:relative;display:inline-block;
}
.try-highlight::after {
    content:'';position:absolute;
    inset-inline-start:2px;inset-inline-end:2px;bottom:-4px;
    height:6px;background:linear-gradient(90deg,#34d399,#059669);
    border-radius:4px;opacity:.75;
}
.try-sub {
    font-size:19px;color:#475569;
    max-width:600px;margin:0 auto 44px;
    line-height:1.8;font-family:'Cairo',sans-serif;
}
.try-trust {
    display:flex;align-items:center;justify-content:center;
    flex-wrap:wrap;gap:10px 26px;
}
.try-trust-item {
    display:inline-flex;align-items:center;gap:7px;
    font-size:13.5px;font-weight:600;color:#64748b;
    font-family:'Cairo',sans-serif;
}
.try-trust-item svg{color:#10b981;flex-shrink:0;}
.try-trust-sep{color:#cbd5e1;font-size:20px;}

/* ──────────────────────────────────
   Steps strip
────────────────────────────────── */
.try-steps-wrap {
    background:#fff;
    border-top:1px solid #e2e8f0;
    border-bottom:1px solid #e2e8f0;
    padding:34px 28px;
}
.try-steps {
    max-width:920px;margin:0 auto;
    display:grid;grid-template-columns:1fr auto 1fr auto 1fr auto 1fr;
    align-items:center;direction:rtl;
}
.try-step {
    display:flex;flex-direction:column;align-items:center;
    text-align:center;gap:12px;padding:0 6px;
}
.try-step-num {
    display:flex;align-items:center;justify-content:center;
    width:52px;height:52px;border-radius:50%;
    background:#ecfdf5;border:2px solid #a7f3d0;
    font-size:19px;font-weight:800;color:#059669;
    font-family:'Cairo',sans-serif;flex-shrink:0;
}
.try-step-label {
    font-size:13.5px;font-weight:700;color:#334155;
    max-width:130px;line-height:1.4;font-family:'Cairo',sans-serif;
}
.try-step-sublabel {
    font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:-4px;
}
.try-step-arrow {
    color:#cbd5e1;font-size:22px;margin-bottom:30px;
    padding:0 4px;transform:scaleX(-1);
}
@media(max-width:640px){
    .try-steps{grid-template-columns:1fr;gap:16px;}
    .try-step-arrow{display:none;}
    .try-step{flex-direction:row;text-align:start;gap:16px;}
    .try-step-label{max-width:none;}
    .try-step-sublabel{display:none;}
}

/* ──────────────────────────────────
   Wizard card
────────────────────────────────── */
.try-wizard-wrap {
    max-width:1040px;margin:0 auto;
    padding:60px 28px 100px;
}
.try-wizard-card {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:28px;
    box-shadow:0 4px 6px -1px #0f172a0a,0 14px 52px -4px #0f172a12;
    padding:52px 52px 48px;
    direction:rtl;
}
@media(max-width:640px){
    .try-wizard-card{padding:24px 18px 30px;border-radius:20px;}
    .try-wizard-wrap{padding:24px 14px 60px;}
}
</style>
@endsection

@section('content')

{{-- HERO --}}
<section class="try-hero" dir="rtl">
    <div class="try-glow-tr"></div>
    <div class="try-glow-bl"></div>
    <div class="try-hero-inner">

        <div class="try-badge">
            <span class="try-badge-dot"></span>
            مجاناً — بدون تسجيل حساب
        </div>

        <h1>سعّر <span class="try-highlight">جدول الكميات</span><br>في ثوانٍ معدودة</h1>

        <p class="try-sub">
            ارفع ملف جدول الكميات وسيقوم الذكاء الاصطناعي بتحليله
            وإحضار الأسعار تلقائياً — من أكثر من <strong>131,000 منتج</strong> موثّق في السوق السعودي.
        </p>

        <div class="try-trust">
            <span class="try-trust-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                131,000+ منتج موثّق
            </span>
            <span class="try-trust-sep">·</span>
            <span class="try-trust-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                نتيجة في أقل من 60 ثانية
            </span>
            <span class="try-trust-sep">·</span>
            <span class="try-trust-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                آمن وسري تماماً
            </span>
            <span class="try-trust-sep">·</span>
            <span class="try-trust-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.87L12 17.77l-6.18 3.24L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                مجاني للمشترين دائماً
            </span>
        </div>

    </div>
</section>

{{-- HOW IT WORKS --}}
<div class="try-steps-wrap" dir="rtl">
    <div class="try-steps">
        <div class="try-step">
            <div class="try-step-num">١</div>
            <div>
                <div class="try-step-label">ارفع ملف BOQ</div>
                <div class="try-step-sublabel">Excel أو PDF أو صورة</div>
            </div>
        </div>
        <div class="try-step-arrow">→</div>
        <div class="try-step">
            <div class="try-step-num">٢</div>
            <div>
                <div class="try-step-label">AI يستخرج البنود</div>
                <div class="try-step-sublabel">تلقائياً بدون تدخل</div>
            </div>
        </div>
        <div class="try-step-arrow">→</div>
        <div class="try-step">
            <div class="try-step-num">٣</div>
            <div>
                <div class="try-step-label">تسعير فوري من السوق</div>
                <div class="try-step-sublabel">أسعار حقيقية ومحدّثة</div>
            </div>
        </div>
        <div class="try-step-arrow">→</div>
        <div class="try-step">
            <div class="try-step-num">٤</div>
            <div>
                <div class="try-step-label">سجّل دخول وحمّل PDF</div>
                <div class="try-step-sublabel">احفظ وشارك عرض السعر</div>
            </div>
        </div>
    </div>
</div>

{{-- WIZARD --}}
<div class="try-wizard-wrap" dir="rtl">
    <div class="try-wizard-card">
        <livewire:enduser.boqs.create-boq :guestMode="true" :guestToken="$guestToken" />
    </div>
</div>

@endsection
