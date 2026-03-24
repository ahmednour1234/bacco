<?php

namespace App\Enums;

enum QuotationProjectStatusEnum: string
{
    case Tender     = 'tender';
    case Pending    = 'pending';
    case Active     = 'active';
    case OnHold     = 'on_hold';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Tender    => 'Tender',
            self::Pending   => 'Pending',
            self::Active    => 'Active',
            self::OnHold    => 'On Hold',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
