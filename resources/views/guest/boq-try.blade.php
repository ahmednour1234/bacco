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
body {
    background: #f6faf8 !important;
}

.try-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(16, 185, 129, .10), transparent 360px),
        linear-gradient(180deg, #f8fffb 0%, #f7fafc 100%);
    font-family: 'Cairo', sans-serif;
    padding: 48px 16px 90px;
}

.try-shell {
    max-width: 1180px;
    margin: 0 auto;
}

.try-header {
    text-align: center;
    margin-bottom: 34px;
}

.try-badge {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 18px;
    border-radius: 999px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #047857;
    font-size: 13px;
    font-weight: 800;
    margin-bottom: 18px;
}

.try-badge span {
    width: 9px;
    height: 9px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 6px rgba(16, 185, 129, .12);
}

.try-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(30px, 4vw, 52px);
    font-weight: 950;
    line-height: 1.18;
}

.try-title strong {
    color: #047857;
}

.try-desc {
    max-width: 690px;
    margin: 18px auto 0;
    color: #64748b;
    font-size: 16px;
    line-height: 1.9;
    font-weight: 500;
}

.try-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 34px;
    box-shadow: 0 22px 70px rgba(15, 23, 42, .09);
    padding: 54px 64px 58px;
    overflow: hidden;
}

.try-card-top {
    margin-bottom: 44px;
}

.try-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 34px;
    position: relative;
}

.try-steps::before {
    content: '';
    position: absolute;
    top: 27px;
    inset-inline: 80px;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
}

.try-step {
    position: relative;
    z-index: 2;
    text-align: center;
}

.try-step-num {
    width: 56px;
    height: 56px;
    margin: 0 auto 12px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #dbe5ef;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 900;
}

.try-step.active .try-step-num {
    background: linear-gradient(135deg, #059669, #10b981);
    border-color: #a7f3d0;
    color: #fff;
    box-shadow: 0 10px 24px rgba(16, 185, 129, .28);
}

.try-step-title {
    color: #94a3b8;
    font-size: 13px;
    font-weight: 800;
}

.try-step.active .try-step-title {
    color: #047857;
}

.try-form-area {
    position: relative;
}

/* ===============================
   Livewire Form Design Override
================================ */

.try-form-area form {
    width: 100%;
}

.try-form-area fieldset,
.try-form-area .section,
.try-form-area [class*="section"] {
    border-radius: 22px !important;
}

.try-form-area label,
.try-form-area .form-label {
    display: block !important;
    margin-bottom: 10px !important;
    color: #0f172a !important;
    font-size: 13px !important;
    font-weight: 900 !important;
    text-transform: uppercase;
    letter-spacing: .02em;
}

.try-form-area .row {
    margin-left: -12px !important;
    margin-right: -12px !important;
    row-gap: 24px !important;
}

.try-form-area .row > [class*="col"] {
    padding-left: 12px !important;
    padding-right: 12px !important;
    margin-bottom: 0 !important;
}

.try-form-area .form-group,
.try-form-area .mb-3,
.try-form-area .mb-4,
.try-form-area [class*="form-group"],
.try-form-area [class*="field"],
.try-form-area [class*="input"] {
    margin-bottom: 26px !important;
}

.try-form-area input,
.try-form-area select,
.try-form-area textarea {
    width: 100% !important;
    min-height: 58px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 16px !important;
    background: #fff !important;
    color: #0f172a !important;
    padding: 15px 18px !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, .04) !important;
    transition: all .2s ease !important;
}

.try-form-area textarea {
    min-height: 150px !important;
    line-height: 1.8 !important;
    resize: vertical;
}

.try-form-area input::placeholder,
.try-form-area textarea::placeholder {
    color: #94a3b8 !important;
}

.try-form-area input:focus,
.try-form-area select:focus,
.try-form-area textarea:focus {
    outline: none !important;
    border-color: #10b981 !important;
    box-shadow: 0 0 0 5px rgba(16, 185, 129, .14) !important;
}

.try-form-area .optional,
.try-form-area small {
    color: #94a3b8 !important;
    font-weight: 600;
    text-transform: none;
}

/* Upload box */
.try-form-area input[type="file"],
.try-form-area [class*="upload"],
.try-form-area [class*="drop"],
.try-form-area [class*="dropzone"] {
    border-radius: 22px !important;
}

.try-form-area input[type="file"] {
    min-height: 74px !important;
    padding: 22px !important;
    background: #f8fafc !important;
    border: 2px dashed #cbd5e1 !important;
}

.try-form-area input[type="file"]:hover,
.try-form-area [class*="dropzone"]:hover {
    border-color: #10b981 !important;
    background: #f0fdf4 !important;
}

/* Section titles */
.try-form-area h1,
.try-form-area h2,
.try-form-area h3,
.try-form-area h4,
.try-form-area h5,
.try-form-area legend,
.try-form-area .section-title {
    color: #0f172a !important;
    font-weight: 950 !important;
    margin-bottom: 18px !important;
}

/* BOQ items area */
.try-form-area table {
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 12px !important;
}

.try-form-area table th {
    color: #64748b !important;
    font-size: 12px !important;
    font-weight: 900 !important;
    padding: 10px 12px !important;
}

.try-form-area table td {
    background: #f8fafc !important;
    border-top: 1px solid #e2e8f0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 12px !important;
}

.try-form-area table td:first-child {
    border-inline-start: 1px solid #e2e8f0 !important;
    border-radius: 14px 0 0 14px !important;
}

.try-form-area table td:last-child {
    border-inline-end: 1px solid #e2e8f0 !important;
    border-radius: 0 14px 14px 0 !important;
}

.try-form-area table input,
.try-form-area table select {
    min-height: 48px !important;
    margin-bottom: 0 !important;
    background: #fff !important;
}

/* Buttons */
.try-form-area button,
.try-form-area .btn {
    min-height: 50px !important;
    border-radius: 15px !important;
    padding: 12px 22px !important;
    font-size: 14px !important;
    font-weight: 900 !important;
    transition: all .2s ease !important;
}

.try-form-area button:hover,
.try-form-area .btn:hover {
    transform: translateY(-1px);
}

.try-form-area .btn-primary,
.try-form-area button[type="submit"] {
    background: linear-gradient(135deg, #047857, #10b981) !important;
    border-color: transparent !important;
    color: #fff !important;
    box-shadow: 0 12px 24px rgba(16, 185, 129, .22) !important;
}

.try-form-area .btn-outline-primary,
.try-form-area .btn-success,
.try-form-area button:not([type]),
.try-form-area button[type="button"] {
    border: 1px solid #86efac !important;
    background: #ecfdf5 !important;
    color: #047857 !important;
}

/* Save Draft button */
.try-form-area button:contains("Save Draft") {
    margin-top: 18px;
}

/* Alerts / errors */
.try-form-area .alert {
    border-radius: 18px !important;
    padding: 16px 18px !important;
    margin-bottom: 24px !important;
}

.try-form-area .text-danger,
.try-form-area .invalid-feedback {
    display: block !important;
    margin-top: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}

/* Remove ugly tight borders */
.try-form-area * {
    box-sizing: border-box;
}

@media (max-width: 992px) {
    .try-card {
        padding: 38px 28px 42px;
        border-radius: 28px;
    }

    .try-steps {
        gap: 18px;
    }

    .try-steps::before {
        inset-inline: 50px;
    }
}

@media (max-width: 768px) {
    .try-page {
        padding: 30px 12px 60px;
    }

    .try-card {
        padding: 26px 16px 32px;
        border-radius: 22px;
    }

    .try-header {
        margin-bottom: 24px;
    }

    .try-title {
        font-size: 30px;
    }

    .try-desc {
        font-size: 14px;
    }

    .try-steps {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .try-steps::before {
        display: none;
    }

    .try-step {
        display: flex;
        align-items: center;
        gap: 14px;
        text-align: start;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
    }

    .try-step-num {
        margin: 0;
        width: 46px;
        height: 46px;
    }

    .try-card-top {
        margin-bottom: 28px;
    }

    .try-form-area .row {
        row-gap: 18px !important;
    }

    .try-form-area .form-group,
    .try-form-area .mb-3,
    .try-form-area .mb-4,
    .try-form-area [class*="form-group"],
    .try-form-area [class*="field"],
    .try-form-area [class*="input"] {
        margin-bottom: 20px !important;
    }

    .try-form-area input,
    .try-form-area select,
    .try-form-area textarea {
        min-height: 52px !important;
        font-size: 14px !important;
    }

    .try-form-area textarea {
        min-height: 125px !important;
    }
}
</style>
@endsection

@section('content')

<div class="try-page" @if($isAr) dir="rtl" @endif>
    <div class="try-shell">

        <div class="try-header">
            <div class="try-badge">
                <span></span>
                {{ $isAr ? 'تجربة مجانية بدون تسجيل' : 'Free trial without registration' }}
            </div>

            <h1 class="try-title">
                @if($isAr)
                    سعّر <strong>جدول الكميات</strong> بسهولة
                @else
                    Price Your <strong>BOQ</strong> Easily
                @endif
            </h1>

            <p class="try-desc">
                {{ $isAr
                    ? 'ارفع ملف جدول الكميات، وسيقوم النظام باستخراج البنود وتسعيرها تلقائياً بطريقة سهلة وسريعة.'
                    : 'Upload your BOQ file, extract items automatically, and get a clean quotation in a few simple steps.' }}
            </p>
        </div>

        <div class="try-card">

            <div class="try-card-top">
                <div class="try-steps">
                    <div class="try-step active">
                        <div class="try-step-num">1</div>
                        <div class="try-step-title">{{ $isAr ? 'استخراج البيانات' : 'Extraction' }}</div>
                    </div>

                    <div class="try-step">
                        <div class="try-step-num">2</div>
                        <div class="try-step-title">{{ $isAr ? 'التأكيد' : 'Confirm' }}</div>
                    </div>

                    <div class="try-step">
                        <div class="try-step-num">3</div>
                        <div class="try-step-title">{{ $isAr ? 'عرض السعر' : 'Quotation' }}</div>
                    </div>

                    <div class="try-step">
                        <div class="try-step-num">4</div>
                        <div class="try-step-title">{{ $isAr ? 'العنوان والدفع' : 'Address & Pay' }}</div>
                    </div>
                </div>
            </div>

            <div class="try-form-area">
                <livewire:enduser.boqs.create-boq :guestMode="true" :guestToken="$guestToken" />
            </div>

        </div>

    </div>
</div>

@endsection
