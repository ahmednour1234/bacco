<?php

namespace App\Enums;

enum QuotationProjectStatusEnum: string
{
    case Pending = 'pending';
    case Tender  = 'tender';
    case Awarded = 'awarded';

    public function label(): string
    {
        return match($this) {
            self::Pending => __('app.status_pending'),
            self::Tender  => __('app.status_tender'),
            self::Awarded => __('app.status_awarded'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
