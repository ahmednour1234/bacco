<?php

namespace App\Enums;

enum QuotationVersionStatusEnum: string
{
    case Draft    = 'draft';
    case Sent     = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired  = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Draft    => __('app.status_draft'),
            self::Sent     => __('app.status_sent'),
            self::Accepted => __('app.status_accepted'),
            self::Rejected => __('app.status_rejected'),
            self::Expired  => __('app.status_expired'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
