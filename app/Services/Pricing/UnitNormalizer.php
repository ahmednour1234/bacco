<?php

namespace App\Services\Pricing;

/**
 * Normalises BOQ units to the approved set and flags units that make no physical
 * sense for the product.
 *
 * This is deliberately deterministic rather than AI-driven: unit rules are fixed,
 * so resolving them in code keeps every line consistent, costs nothing, and lets
 * the AI budget go to the judgement calls that actually need it.
 */
final class UnitNormalizer
{
    /** The approved unit set. */
    public const APPROVED = [
        'PCS', 'SET', 'M', 'M2', 'M3', 'KG', 'TON', 'LTR',
        'ROLL', 'BOX', 'BAG', 'DRUM', 'LOT', 'LICENSE', 'YEAR',
    ];

    /**
     * Spelling variants → canonical unit. Arabic and English, since BOQs mix both.
     */
    private const ALIASES = [
        // countable
        'pcs' => 'PCS', 'pc' => 'PCS', 'piece' => 'PCS', 'pieces' => 'PCS', 'ea' => 'PCS',
        'each' => 'PCS', 'no' => 'PCS', 'no.' => 'PCS', 'nos' => 'PCS', 'unit' => 'PCS',
        'units' => 'PCS', 'قطعة' => 'PCS', 'قطع' => 'PCS', 'عدد' => 'PCS', 'حبة' => 'PCS',
        // grouped
        'set' => 'SET', 'sets' => 'SET', 'kit' => 'SET', 'طقم' => 'SET', 'مجموعة' => 'SET',
        // linear
        'm' => 'M', 'mt' => 'M', 'mtr' => 'M', 'meter' => 'M', 'meters' => 'M', 'metre' => 'M',
        'lm' => 'M', 'l.m' => 'M', 'متر' => 'M', 'متر طولي' => 'M', 'م.ط' => 'M',
        // area
        'm2' => 'M2', 'sqm' => 'M2', 'sq.m' => 'M2', 'sq m' => 'M2', 'square meter' => 'M2',
        'م2' => 'M2', 'متر مربع' => 'M2', 'م.م' => 'M2',
        // volume
        'm3' => 'M3', 'cbm' => 'M3', 'cu.m' => 'M3', 'cubic meter' => 'M3',
        'م3' => 'M3', 'متر مكعب' => 'M3', 'م.ك' => 'M3',
        // weight
        'kg' => 'KG', 'kgs' => 'KG', 'kilogram' => 'KG', 'كجم' => 'KG', 'كيلو' => 'KG',
        'ton' => 'TON', 'tons' => 'TON', 'tonne' => 'TON', 'mt.' => 'TON', 'طن' => 'TON',
        // liquid
        'ltr' => 'LTR', 'l' => 'LTR', 'lt' => 'LTR', 'liter' => 'LTR', 'litre' => 'LTR',
        'gallon' => 'LTR', 'لتر' => 'LTR',
        // packaged
        'roll' => 'ROLL', 'rolls' => 'ROLL', 'لفة' => 'ROLL',
        'box' => 'BOX', 'boxes' => 'BOX', 'ctn' => 'BOX', 'carton' => 'BOX', 'علبة' => 'BOX', 'كرتون' => 'BOX',
        'bag' => 'BAG', 'bags' => 'BAG', 'شيكارة' => 'BAG', 'كيس' => 'BAG',
        'drum' => 'DRUM', 'drums' => 'DRUM', 'برميل' => 'DRUM',
        'lot' => 'LOT', 'ls' => 'LOT', 'lump sum' => 'LOT', 'مقطوعية' => 'LOT',
        // intangible
        'license' => 'LICENSE', 'licence' => 'LICENSE', 'lic' => 'LICENSE', 'رخصة' => 'LICENSE',
        'year' => 'YEAR', 'yr' => 'YEAR', 'annual' => 'YEAR', 'سنة' => 'YEAR',
    ];

    /**
     * Units that are physically impossible for a given catalog family. Anything
     * here is a hard error, not a preference — e.g. a laptop measured in metres.
     *
     * @return array<string, list<string>> family key => units that are never valid
     */
    private const IMPOSSIBLE = [
        // discrete goods can never be linear/area/volume/weight
        'laptop'   => ['M', 'M2', 'M3', 'KG', 'TON', 'LTR'],
        'desktop'  => ['M', 'M2', 'M3', 'KG', 'TON', 'LTR'],
        'monitor'  => ['M', 'M2', 'M3', 'KG', 'TON', 'LTR'],
        'server'   => ['M', 'M2', 'M3', 'KG', 'TON', 'LTR'],
        'printer'  => ['M', 'M2', 'M3', 'KG', 'TON', 'LTR'],
        'ip_camera'=> ['M', 'M2', 'M3', 'KG', 'TON', 'LTR'],
        'ups'      => ['M', 'M2', 'M3', 'LTR'],
        'office_desk'  => ['KG', 'TON', 'LTR', 'M3'],
        'office_chair' => ['KG', 'TON', 'LTR', 'M3', 'M2'],
        // bulk materials can never be counted as pieces
        'ready_mix' => ['PCS', 'M', 'M2', 'LTR', 'BOX'],
        'rebar'     => ['PCS', 'M2', 'M3', 'LTR'],
        'tiles'     => ['LTR', 'M3', 'TON'],
        'paint'     => ['PCS', 'M3', 'TON'],
        // cable must carry a length
        'data_cable'  => ['PCS', 'M2', 'M3', 'KG', 'LTR'],
        'power_cable' => ['PCS', 'M2', 'M3', 'LTR'],
    ];

    /**
     * Normalise one unit.
     *
     * @return array{
     *   normalized: string|null,
     *   warnings: list<array{code:string, severity:string, message:string}>
     * }
     */
    public static function normalize(?string $rawUnit, ?array $catalogEntry = null): array
    {
        $warnings = [];
        $raw      = trim((string) $rawUnit);

        if ($raw === '') {
            return [
                'normalized' => $catalogEntry['unit'] ?? null,
                'warnings'   => [[
                    'code'     => 'UNIT_MISSING',
                    'severity' => $catalogEntry ? 'info' : 'warning',
                    'message'  => $catalogEntry
                        ? "Unit was empty; defaulted to the standard unit for this product ({$catalogEntry['unit']})."
                        : 'Unit is missing and no standard unit could be inferred.',
                ]],
            ];
        }

        $key        = self::key($raw);
        $normalized = self::ALIASES[$key] ?? (in_array(strtoupper($raw), self::APPROVED, true) ? strtoupper($raw) : null);

        if ($normalized === null) {
            return [
                'normalized' => $catalogEntry['unit'] ?? null,
                'warnings'   => [[
                    'code'     => 'UNIT_UNRECOGNISED',
                    'severity' => 'warning',
                    'message'  => "Unit \"{$raw}\" is not one of the approved units."
                        . ($catalogEntry ? " Defaulted to {$catalogEntry['unit']}." : ''),
                ]],
            ];
        }

        // Physically impossible for this product family → hard error.
        $family = $catalogEntry['key'] ?? null;
        if ($family !== null && in_array($normalized, self::IMPOSSIBLE[$family] ?? [], true)) {
            $warnings[] = [
                'code'     => 'UNIT_INVALID_FOR_PRODUCT',
                'severity' => 'error',
                'message'  => "Unit \"{$normalized}\" is not valid for this product type; expected {$catalogEntry['unit']}.",
            ];

            return ['normalized' => $catalogEntry['unit'], 'warnings' => $warnings];
        }

        // Valid unit, but not the family default — worth noting, not correcting.
        if ($family !== null && $normalized !== $catalogEntry['unit']) {
            $warnings[] = [
                'code'     => 'UNIT_NON_STANDARD',
                'severity' => 'info',
                'message'  => "Unit \"{$normalized}\" differs from the standard unit for this product ({$catalogEntry['unit']}); confirm it is intended.",
            ];
        }

        return ['normalized' => $normalized, 'warnings' => $warnings];
    }

    /** Case/space-insensitive lookup key. */
    private static function key(string $unit): string
    {
        $u = mb_strtolower(trim($unit));
        $u = strtr($u, ['²' => '2', '³' => '3']);
        return preg_replace('/\s+/u', ' ', $u);
    }
}
