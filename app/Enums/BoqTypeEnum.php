<?php

namespace App\Enums;

enum BoqTypeEnum: string
{
    case Tender  = 'tender';
    case Awarded = 'awarded';

    public function label(): string
    {
        return match($this) {
            self::Tender  => __('app.boq_type_tender'),
            self::Awarded => __('app.boq_type_awarded'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
