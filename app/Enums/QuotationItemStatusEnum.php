<?php

namespace App\Enums;

enum QuotationItemStatusEnum: string
{
    case Pending  = 'pending';
    case Sourcing = 'sourcing';
    case Sourced  = 'sourced';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending  => 'Pending',
            self::Sourcing => 'Sourcing',
            self::Sourced  => 'Sourced',
            self::Rejected => 'Rejected',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
