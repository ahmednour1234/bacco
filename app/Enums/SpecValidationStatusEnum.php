<?php

namespace App\Enums;

/**
 * Result of the pre-pricing spec-validation pass on a BOQ / quotation item.
 */
enum SpecValidationStatusEnum: string
{
    case Valid            = 'valid';
    case UnitError        = 'unit_error';
    case NeedsInformation = 'needs_information';

    /** Localised label (ar/en) for display. */
    public function label(): string
    {
        $isAr = app()->getLocale() === 'ar';

        return match ($this) {
            self::Valid            => $isAr ? 'مكتمل' : 'Valid',
            self::UnitError        => $isAr ? 'وحدة غير صحيحة' : 'Unit issue',
            self::NeedsInformation => $isAr ? 'مواصفات ناقصة' : 'Needs info',
        };
    }

    /** Tailwind classes for a status badge. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Valid            => 'bg-emerald-50 text-emerald-700',
            self::UnitError        => 'bg-amber-50 text-amber-700',
            self::NeedsInformation => 'bg-red-50 text-red-600',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Safe casting from a nullable free string stored in the DB. */
    public static function tryFromString(?string $value): ?self
    {
        return $value !== null ? self::tryFrom($value) : null;
    }
}
