<?php

namespace App\Enums\Catalog\Research;

/** Which physical component of a model a material applies to. */
enum ComponentTypeEnum: string
{
    case Body   = 'body';
    case Ball   = 'ball';
    case Stem   = 'stem';
    case Seat   = 'seat';
    case Seal   = 'seal';
    case Handle = 'handle';
    case Trim   = 'trim';
    case Other  = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Body   => 'Body',
            self::Ball   => 'Ball',
            self::Stem   => 'Stem',
            self::Seat   => 'Seat',
            self::Seal   => 'Seal',
            self::Handle => 'Handle',
            self::Trim   => 'Trim',
            self::Other  => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
