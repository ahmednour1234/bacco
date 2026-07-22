<?php

namespace App\Enums\Catalog\Research;

/** Health of a source document URL (checked/rechecked over time). */
enum SourceStatusEnum: string
{
    case Active     = 'active';
    case Unreachable = 'unreachable';
    case Moved      = 'moved';
    case Flagged    = 'flagged';
    case Archived   = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active      => 'Active',
            self::Unreachable => 'Unreachable',
            self::Moved       => 'Moved',
            self::Flagged     => 'Flagged',
            self::Archived    => 'Archived',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
