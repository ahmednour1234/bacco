<?php

namespace App\Services\Catalog\Research;

use Illuminate\Support\Str;

/**
 * Pure, side-effect-free normalization of catalog values. No DB, no AI — just
 * string → canonical-form logic so the same physical value written many ways
 * collapses to one key.
 *
 * This is what makes deduplication and idempotent research writes possible:
 *   1 1/4"  1¼"  1.25 inch  DN32   → the same normalized size
 *   NPT  N.P.T.  Female NPT  FNPT  → the same normalized connection
 *
 * The engine NEVER invents values and NEVER builds cartesian combinations — it
 * only canonicalizes what it is given.
 */
class NormalizationEngine
{
    /** Fraction glyphs → their ascii "a/b" form. */
    private const FRACTION_GLYPHS = [
        '¼' => '1/4', '½' => '1/2', '¾' => '3/4',
        '⅐' => '1/7', '⅑' => '1/9', '⅒' => '1/10',
        '⅓' => '1/3', '⅔' => '2/3',
        '⅕' => '1/5', '⅖' => '2/5', '⅗' => '3/5', '⅘' => '4/5',
        '⅙' => '1/6', '⅚' => '5/6',
        '⅛' => '1/8', '⅜' => '3/8', '⅝' => '5/8', '⅞' => '7/8',
    ];

    /** Common DN ↔ inch equivalences for cross-matching. */
    private const DN_TO_INCH = [
        8 => 0.25, 10 => 0.375, 15 => 0.5, 20 => 0.75, 25 => 1.0,
        32 => 1.25, 40 => 1.5, 50 => 2.0, 65 => 2.5, 80 => 3.0,
        100 => 4.0, 125 => 5.0, 150 => 6.0, 200 => 8.0,
    ];

    /** Lower-case, collapse whitespace, trim. */
    public function normalizeText(?string $value): string
    {
        return Str::of((string) $value)->lower()->squish()->value();
    }

    /**
     * A stable key for any free-text token used in the variant key: strips all
     * punctuation and spacing so "KT-585-70-UL" and "kt58570ul" match.
     */
    public function normalizeToken(?string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', $this->normalizeText($value)) ?? '';
    }

    /**
     * Canonicalize a size. Returns [normalized, inch_decimal|null, dn|null].
     *
     * @return array{normalized:string, inch:?float, dn:?int}
     */
    public function normalizeSize(?string $raw): array
    {
        $text = $this->normalizeText($raw);
        if ($text === '') {
            return ['normalized' => '', 'inch' => null, 'dn' => null];
        }

        // DNxx form.
        if (preg_match('/\bdn\s*(\d+(?:\.\d+)?)/', $text, $m)) {
            $dn   = (int) round((float) $m[1]);
            $inch = self::DN_TO_INCH[$dn] ?? null;

            return [
                'normalized' => 'dn' . $dn,
                'inch'       => $inch,
                'dn'         => $dn,
            ];
        }

        // Replace fraction glyphs. A glyph directly after a digit ("1¼") is a
        // mixed number, so insert a space → "1 1/4" (otherwise it becomes 11/4).
        $spaced   = preg_replace('/(\d)([¼½¾⅐⅑⅒⅓⅔⅕⅖⅗⅘⅙⅚⅛⅜⅝⅞])/u', '$1 $2', $text) ?? $text;
        $expanded = strtr($spaced, self::FRACTION_GLYPHS);
        $expanded = str_replace(['"', '”', 'inch', 'inches', 'in.', ' in'], ['', '', '', '', '', ''], $expanded);
        $expanded = trim($expanded);

        $inch = $this->parseInchValue($expanded);

        if ($inch !== null) {
            // Canonical: strip trailing zeros (1.50 → 1.5).
            $canonical = rtrim(rtrim(number_format($inch, 4, '.', ''), '0'), '.');
            $dn        = array_search((float) $canonical, self::DN_TO_INCH, true) ?: null;

            return [
                'normalized' => $canonical . 'in',
                'inch'       => (float) $canonical,
                'dn'         => $dn ?: null,
            ];
        }

        // Not numeric — fall back to a squished token so it still keys stably.
        return ['normalized' => $this->normalizeToken($text), 'inch' => null, 'dn' => null];
    }

    /**
     * Canonicalize a pressure rating.
     * Returns [normalized, numeric|null, unit|null, class|null].
     *
     * @return array{normalized:string, numeric:?float, unit:?string, class:?string}
     */
    public function normalizePressure(?string $raw): array
    {
        $text = $this->normalizeText($raw);
        if ($text === '') {
            return ['normalized' => '', 'numeric' => null, 'unit' => null, 'class' => null];
        }

        // PN16, PN 16
        if (preg_match('/\bpn\s*(\d+)/', $text, $m)) {
            return ['normalized' => 'pn' . $m[1], 'numeric' => (float) $m[1], 'unit' => 'bar', 'class' => 'PN'];
        }

        // Class 150, class150
        if (preg_match('/\bclass\s*(\d+)/', $text, $m)) {
            return ['normalized' => 'class' . $m[1], 'numeric' => (float) $m[1], 'unit' => 'class', 'class' => 'Class'];
        }

        // 300 PSI, 600 WOG, 150 WSP
        if (preg_match('/(\d+(?:\.\d+)?)\s*(psi|wog|wsp|bar|kpa|mpa)?/', $text, $m) && $m[1] !== '') {
            $num  = (float) $m[1];
            $unit = $m[2] ?? '';
            $class = in_array($unit, ['wog', 'wsp'], true) ? Str::upper($unit) : null;
            $u    = in_array($unit, ['wog', 'wsp', ''], true) ? 'psi' : $unit;

            return [
                'normalized' => (string) ((int) $num) . ($unit !== '' ? $unit : 'psi'),
                'numeric'    => $num,
                'unit'       => $u,
                'class'      => $class,
            ];
        }

        return ['normalized' => $this->normalizeToken($text), 'numeric' => null, 'unit' => null, 'class' => null];
    }

    /**
     * Canonicalize a connection descriptor. NPT / N.P.T. / FNPT / Female NPT all
     * reduce toward "npt" while preserving gender when present.
     */
    public function normalizeConnection(?string $raw): string
    {
        $text = $this->normalizeText($raw);
        if ($text === '') {
            return '';
        }

        // Remove dots inside acronyms: n.p.t. → npt
        $text = preg_replace('/(?<=\p{L})\.(?=\p{L})/u', '', $text) ?? $text;

        $female = (bool) preg_match('/\b(female|fnpt|f\b)/', $text);
        $male   = (bool) preg_match('/\b(male|mnpt|m\b)/', $text);

        $base = match (true) {
            str_contains($text, 'npt')        => 'npt',
            str_contains($text, 'bspt')       => 'bspt',
            str_contains($text, 'bsp')        => 'bsp',
            str_contains($text, 'press')      => 'press',
            str_contains($text, 'solder'),
            str_contains($text, 'sweat')      => 'solder',
            str_contains($text, 'groove')     => 'grooved',
            str_contains($text, 'flange')     => 'flanged',
            str_contains($text, 'push')       => 'push-fit',
            str_contains($text, 'pex')        => 'pex',
            str_contains($text, 'compression') => 'compression',
            str_contains($text, 'union')      => 'union',
            default                            => $this->normalizeToken($text),
        };

        $gender = $female ? 'female-' : ($male ? 'male-' : '');

        return $gender . $base;
    }

    /**
     * Build the unique dedup / idempotency key for a variant.
     *
     * With a real SKU:  manufacturer|model|sku|size|connection|pressure
     * Without a SKU:    manufacturer|model|size|connection|pressure
     *
     * Example: nibco|kt58570ul|nl95046|0.5in|female-npt|300psi
     */
    public function variantKey(
        ?string $manufacturer,
        ?string $model,
        ?string $sku,
        ?string $normalizedSize,
        ?string $normalizedConnection,
        ?string $normalizedPressure,
    ): string {
        $parts = [
            $this->normalizeToken($manufacturer),
            $this->normalizeToken($model),
        ];

        $skuToken = $this->normalizeToken($sku);
        if ($skuToken !== '') {
            $parts[] = $skuToken;
        }

        $parts[] = $normalizedSize ?? '';
        $parts[] = $normalizedConnection ?? '';
        $parts[] = $normalizedPressure ?? '';

        return implode('|', $parts);
    }

    /**
     * A stable hash of an entire imported row, used to reject re-importing the
     * exact same row twice within one file.
     */
    public function rowHash(array $values): string
    {
        $canonical = array_map(fn ($v) => $this->normalizeText((string) $v), $values);

        return hash('sha256', implode('¦', $canonical));
    }

    /**
     * Split a multi-value cell ("NIBCO, KITZ\nVictaulic") into clean tokens.
     *
     * @return array<int,string>
     */
    public function splitMultiValue(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $parts = preg_split('/[\r\n,;\/|•]+/u', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
    }

    private function parseInchValue(string $expanded): ?float
    {
        // "1 1/4" or "1-1/4"
        if (preg_match('#^(\d+)[\s\-]+(\d+)\s*/\s*(\d+)$#', $expanded, $m)) {
            return (float) $m[1] + ((int) $m[2] / (int) $m[3]);
        }
        // pure fraction "3/4"
        if (preg_match('#^(\d+)\s*/\s*(\d+)$#', $expanded, $m)) {
            return (int) $m[1] / (int) $m[2];
        }
        // decimal / integer "1.25" or "2"
        if (preg_match('#^(\d+(?:\.\d+)?)$#', $expanded, $m)) {
            return (float) $m[1];
        }

        return null;
    }
}
