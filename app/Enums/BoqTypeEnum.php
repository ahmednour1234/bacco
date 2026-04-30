<?php

namespace App\Enums;

enum BoqTypeEnum: string
{
    case Tender  = 'tender';
    case Awarded = 'awarded';

    public function label(): string
    {
        return match($this) {
            self::Tender  => 'Tender (Bidding)',
            self::Awarded => 'On-hand (Awarded)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
