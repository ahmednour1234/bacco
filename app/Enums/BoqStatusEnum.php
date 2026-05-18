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
            self::Draft     => __('app.status_draft'),
            self::Submitted => __('app.status_submitted'),
            self::Completed => __('app.status_completed'),
            self::Cancelled => __('app.status_cancelled'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
