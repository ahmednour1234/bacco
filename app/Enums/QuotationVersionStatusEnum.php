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
            self::Draft    => 'Draft',
            self::Sent     => 'Sent',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Expired  => 'Expired',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
