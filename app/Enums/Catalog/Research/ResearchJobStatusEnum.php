<?php

namespace App\Enums\Catalog\Research;

enum ResearchJobStatusEnum: string
{
    case Pending             = 'pending';
    case Queued              = 'queued';
    case Processing          = 'processing';
    case AwaitingValidation  = 'awaiting_validation';
    case Completed           = 'completed';
    case PartiallyCompleted  = 'partially_completed';
    case Failed              = 'failed';
    case Cancelled           = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending            => 'Pending',
            self::Queued             => 'Queued',
            self::Processing         => 'Processing',
            self::AwaitingValidation => 'Awaiting Validation',
            self::Completed          => 'Completed',
            self::PartiallyCompleted => 'Partially Completed',
            self::Failed             => 'Failed',
            self::Cancelled          => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
