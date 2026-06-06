@extends('layouts.app')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', $isAr ? 'سعّر جدول الكميات مجاناً — كيمتا' : 'Price Your BOQ Free — Qimta')
@section('description', $isAr
    ? 'ارفع جدول الكميات واحصل على تسعيرة فورية مجانية — بدون تسجيل مسبق.'
    : 'Upload your BOQ and get instant pricing for free — no account required to start.')

@section('no_ar_hreflang', true)

@section('nav-cta')
    @auth
        <a href="{{ route('enduser.boqs.create') }}" class="btn-nav-cta">
            {{ $isAr ? 'إنشاء BOQ جديد' : 'New BOQ' }}
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    @else
        <a href="{{ route('enduser.login') }}" class="btn-nav-cta">
            {{ $isAr ? 'تسجيل الدخول' : 'Sign in' }}
        </a>
    @endauth
@endsection

@section('mobile-cta')
    @guest
        <a href="{{ route('enduser.login') }}" class="btn btn-primary">
            {{ $isAr ? 'تسجيل الدخول' : 'Sign in' }}
        </a>
    @endguest
@endsection

@section('styles')
<style>
    .try-hero {
        padding: 80px 0 40px;
        background: var(--white);
    }

    .try-hero-inner {
        max-width: 760px;
        margin: 0 auto;
        text-align: center;
        padding: 0 24px;
    }

    .try-hero h1 {
        font-family: 'Cairo', sans-serif;
        font-size: clamp(32px, 4.5vw, 52px);
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -1.5px;
        color: var(--dark);
        margin-bottom: 14px;
    }

    .try-hero p {
        font-size: 18px;
        color: var(--slate);
        max-width: 580px;
        margin: 0 auto 10px;
        line-height: 1.6;
    }

    .try-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #059669;
        font-size: 13px;
        font-weight: 700;
        border-radius: 50px;
        padding: 5px 14px;
        margin-bottom: 20px;
    }

    .try-badge svg {
        flex-shrink: 0;
    }

    .try-content {
        max-width: 960px;
        margin: 0 auto;
        padding: 0 24px 80px;
    }
</style>
@endsection

@section('content')
<section class="try-hero">
    <div class="try-hero-inner">
        <span class="try-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            {{ $isAr ? 'مجاناً — بدون تسجيل' : 'Free — No account required to start' }}
        </span>
        <h1>
            {{ $isAr ? 'سعّر جدول الكميات في ثوانٍ' : 'Price Your BOQ in Seconds' }}
        </h1>
        <p>
            {{ $isAr
                ? 'ارفع ملف جدول الكميات وسيقوم الذكاء الاصطناعي بتحليله وإحضار الأسعار تلقائياً.'
                : 'Upload your BOQ file and our AI will extract line items and fetch live prices automatically.' }}
        </p>
    </div>
</section>

<div class="try-content">
    <livewire:enduser.boqs.create-boq :guestMode="true" :guestToken="$guestToken" />
</div>
@endsection
