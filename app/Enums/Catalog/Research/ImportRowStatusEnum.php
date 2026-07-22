<?php

namespace App\Enums\Catalog\Research;

/** Per-row status for an imported Excel source row. Raw row is never deleted. */
enum ImportRowStatusEnum: string
{
    case Pending             = 'pending';
    case Imported            = 'imported';
    case Duplicate           = 'duplicate';
    case Failed              = 'failed';
    case MissingDescription  = 'missing_description';
    case ReadyForResearch    = 'ready_for_research';
    case RequiresReview      = 'requires_review';

    public function label(): string
    {
        return match ($this) {
            self::Pending            => 'Pending',
            self::Imported           => 'Imported',
            self::Duplicate          => 'Duplicate',
            self::Failed             => 'Failed',
            self::MissingDescription => 'Missing Description',
            self::ReadyForResearch   => 'Ready for Research',
            self::RequiresReview     => 'Requires Review',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
