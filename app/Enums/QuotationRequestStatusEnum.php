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
            self::Draft     => 'Draft',
            self::Submitted => 'Submitted',
            self::Tender    => 'Tender',
            self::InReview  => 'In Review',
            self::Quoted    => 'Quoted',
            self::Accepted  => 'Accepted',
            self::Rejected  => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
