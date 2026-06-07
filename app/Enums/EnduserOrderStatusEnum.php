<?php

namespace App\Enums;

enum EnduserOrderStatusEnum: string
{
    case OpenUnpaid             = 'open_unpaid';
    case OpenReceiptUnderReview = 'open_receipt_under_review';
    case OpenPaymentConfirmed   = 'open_payment_confirmed';
    case Closed                 = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OpenUnpaid             => __('app.enduser_order_status_open_unpaid'),
            self::OpenReceiptUnderReview => __('app.enduser_order_status_open_receipt_under_review'),
            self::OpenPaymentConfirmed   => __('app.enduser_order_status_open_payment_confirmed'),
            self::Closed                 => __('app.enduser_order_status_closed'),
        };
    }

    public function message(): string
    {
        return match ($this) {
            self::OpenUnpaid             => __('app.enduser_order_status_open_unpaid_msg'),
            self::OpenReceiptUnderReview => __('app.enduser_order_status_open_receipt_under_review_msg'),
            self::OpenPaymentConfirmed   => __('app.enduser_order_status_open_payment_confirmed_msg'),
            self::Closed                 => __('app.enduser_order_status_closed_msg'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OpenUnpaid             => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            self::OpenReceiptUnderReview => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
            self::OpenPaymentConfirmed   => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            self::Closed                 => 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',
        };
    }

    public function leftBorderClass(): string
    {
        return match ($this) {
            self::OpenUnpaid             => 'border-l-amber-400',
            self::OpenReceiptUnderReview => 'border-l-blue-400',
            self::OpenPaymentConfirmed   => 'border-l-emerald-400',
            self::Closed                 => 'border-l-slate-300',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::OpenUnpaid             => 'bg-amber-400',
            self::OpenReceiptUnderReview => 'bg-blue-500',
            self::OpenPaymentConfirmed   => 'bg-emerald-500',
            self::Closed                 => 'bg-slate-400',
        };
    }

    public function textClass(): string
    {
        return match ($this) {
            self::OpenUnpaid             => 'text-amber-700',
            self::OpenReceiptUnderReview => 'text-blue-700',
            self::OpenPaymentConfirmed   => 'text-emerald-700',
            self::Closed                 => 'text-slate-600',
        };
    }

    public function iconClass(): string
    {
        return match ($this) {
            self::OpenUnpaid             => 'text-amber-500',
            self::OpenReceiptUnderReview => 'text-blue-500',
            self::OpenPaymentConfirmed   => 'text-emerald-500',
            self::Closed                 => 'text-slate-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
