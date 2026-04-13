<?php

namespace App\Enums;

enum BoqStatusEnum: string
{
    case Draft     = 'draft';
    case Submitted = 'submitted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Submitted => 'Submitted',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
