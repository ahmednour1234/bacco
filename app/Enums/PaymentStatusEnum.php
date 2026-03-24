<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Pending   = 'pending';
    case Submitted = 'submitted';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending',
            self::Submitted => 'Submitted',
            self::Approved  => 'Approved',
            self::Rejected  => 'Rejected',
            self::Refunded  => 'Refunded',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
