<?php

namespace App\Enums;

enum NotificationChannelEnum: string
{
    case Database = 'database';
    case Email    = 'email';
    case Sms      = 'sms';

    public function label(): string
    {
        return match($this) {
            self::Database => 'Database',
            self::Email    => 'Email',
            self::Sms      => 'SMS',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
