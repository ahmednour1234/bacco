<?php

namespace App\Enums;

enum QuotationRequestStatusEnum: string
{
    case Draft      = 'draft';
    case Submitted  = 'submitted';
    case Tender     = 'tender';
    case InReview   = 'in_review';
    case Quoted     = 'quoted';
    case Accepted   = 'accepted';
    case Rejected   = 'rejected';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft     => __('app.status_draft'),
            self::Submitted => __('app.status_submitted'),
            self::Tender    => __('app.status_tender'),
            self::InReview  => __('app.status_in_review'),
            self::Quoted    => __('app.status_quoted'),
            self::Accepted  => __('app.status_accepted'),
            self::Rejected  => __('app.status_rejected'),
            self::Cancelled => __('app.status_cancelled'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
