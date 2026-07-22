<?php

namespace App\Services\Catalog\Pricing;

use App\Services\Catalog\Research\NormalizationEngine;
use Illuminate\Support\Str;

/**
 * Extracts matchable specifications from a BOQ line.
 *
 * BOQ text is written by engineers in free form and often mixes Arabic and
 * English ("صمام كرة نحاس 2 بوصة threaded"), so parsing is deliberately
 * tolerant: it pulls out whatever it can recognise and leaves the rest null
 * rather than guessing. A null spec simply is not matched on — an invented one
 * would silently link the wrong product.
 */
class BoqSpecParser
{
    public function __construct(private readonly NormalizationEngine $engine) {}

    /**
     * @return array{
     *   raw:string, normalized:string, sku:?string, brand:?string,
     *   model:?string, size:?string, material:?string, connection:?string,
     *   pressure:?string, keywords:list<string>
     * }
     */
    public function parse(string $description, ?string $brand = null): array
    {
        $raw  = trim($description);
        $text = $this->translateCommonTerms($raw);

        return [
            'raw'        => $raw,
            'normalized' => $this->engine->normalizeText($text),
            'sku'        => $this->extractSku($raw),
            'brand'      => $brand ? trim($brand) : $this->extractBrand($text),
            'model'      => $this->extractModel($raw),
            'size'       => $this->extractSize($text),
            'material'   => $this->extractMaterial($text),
            'connection' => $this->extractConnection($text),
            'pressure'   => $this->extractPressure($text),
            'keywords'   => $this->keywords($text),
        ];
    }

    /**
     * Pull the size PHRASE out of the sentence, then normalize only that.
     *
     * The shared NormalizationEngine expects a clean value like "2 inch"; given
     * a whole BOQ line it normalizes the entire sentence, which then matches
     * nothing. So the phrase is isolated first.
     */
    private function extractSize(string $text): ?string
    {
        // DN is a size, not a model, so read it before model numbers are
        // stripped below (DN100 otherwise looks exactly like a part number).
        if (preg_match('/\bDN\s*(\d{1,4})\b/i', $text, $dn)) {
            $size = $this->engine->normalizeSize('DN' . $dn[1]);

            return $size['normalized'] ?: null;
        }

        // Strip model/part numbers. Otherwise "VK102 1/2 inch" reads as
        // "102 1/2" and yields a 102.5 inch size that matches nothing real.
        $text = preg_replace('/\b[A-Z]{1,6}[-\s]?\d{2,6}[A-Z0-9-]*\b/i', ' ', $text) ?? $text;
        // Arabic keeps its own numerals and word order ("2 بوصة"), and BOQ text
        // often writes inches as a bare quote (1/2"), so both are matched here
        // rather than relying on the English-only forms.
        $patterns = [
            '/\bDN\s*(\d{1,4})\b/i',                                        // DN50
            '/(\d+\s*\d*\/\d+)\s*(?:"|″|inch|inches|in\b|بوصة|بوصه)/iu',    // 1 1/2", 1/2 بوصة
            '/(\d+(?:\.\d+)?)\s*(?:"|″|inch|inches|in\b|بوصة|بوصه)/iu',     // 2 inch, 2", 2 بوصة
            '/(\d{1,4})\s*(?:mm|millimet|ملم|مم)/iu',                       // 50 mm, 50 ملم
        ];

        foreach ($patterns as $i => $pattern) {
            if (! preg_match($pattern, $text, $m)) {
                continue;
            }

            // Rebuild a clean value the engine can understand.
            $phrase = match (true) {
                $i === 0 => 'DN' . $m[1],
                $i === 3 => $m[1] . ' mm',
                default  => $m[1] . ' inch',
            };

            $size = $this->engine->normalizeSize($phrase);

            return $size['normalized'] ?: null;
        }

        return null;
    }

    /**
     * Pressure must be stated with a unit. A bare number in a BOQ line is a
     * quantity or a size — reading it as a rating would attach a fabricated
     * spec to the match.
     */
    private function extractPressure(string $text): ?string
    {
        $patterns = [
            '/\b(\d{1,5})\s*(psi|bar|kpa|mpa)\b/i',
            '/\b(?:PN|CLASS)\s*(\d{1,4})\b/i',
            '/\b(\d{2,4})\s*(?:WOG|WSP)\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $pressure = $this->engine->normalizePressure(trim($m[0]));

                return $pressure['normalized'] ?: null;
            }
        }

        return null;
    }

    /**
     * Only recognised connection words count. Passing the whole line to the
     * engine returns the sentence itself, which matches nothing.
     */
    private function extractConnection(string $text): ?string
    {
        $terms = [
            'female threaded', 'male threaded', 'threaded', 'npt', 'bspt', 'bsp',
            'press-fit', 'press fit', 'grooved', 'flanged', 'flange', 'solder',
            'sweat', 'push-fit', 'compression', 'welded', 'butt weld',
            'socket weld', 'union', 'pex',
        ];

        $haystack = ' ' . mb_strtolower($text) . ' ';

        foreach ($terms as $term) {
            if (str_contains($haystack, $term)) {
                return $this->engine->normalizeConnection($term) ?: null;
            }
        }

        return null;
    }

    /**
     * Map common Arabic engineering terms to English so one matching path
     * serves both languages. Only well-established equivalences are listed —
     * a wrong translation would corrupt the match.
     *
     * @var array<string,string>
     */
    private const AR_TERMS = [
        'صمام'      => 'valve',
        'محبس'      => 'valve',
        'كرة'       => 'ball',
        'بوابة'     => 'gate',
        'فراشة'     => 'butterfly',
        'عدم رجوع'  => 'check',
        'نحاس'      => 'brass',
        'برونز'     => 'bronze',
        'حديد'      => 'iron',
        'صلب'       => 'steel',
        'ستانلس'    => 'stainless steel',
        'مقاوم للصدأ' => 'stainless steel',
        'بلاستيك'   => 'plastic',
        'ماسورة'    => 'pipe',
        'مواسير'    => 'pipe',
        'كابل'      => 'cable',
        'كيبل'      => 'cable',
        'قاطع'      => 'breaker',
        'لوحة'      => 'panel',
        'مضخة'      => 'pump',
        'طلمبة'     => 'pump',
        'مروحة'     => 'fan',
        'كاشف'      => 'detector',
        'حساس'      => 'sensor',
        'رشاش'      => 'sprinkler',
        'إنذار'     => 'alarm',
        'انذار'     => 'alarm',
        'حريق'      => 'fire',
        'مقاس'      => 'size',
        'قطر'       => 'diameter',
        'بوصة'      => 'inch',
        'بوصه'      => 'inch',
        'ملم'       => 'mm',
        'مم'        => 'mm',
        'ضغط'       => 'pressure',
        'كهربائي'   => 'electrical',
        'مجلفن'     => 'galvanized',
        'ملولب'     => 'threaded',
        'فلنجة'     => 'flanged',
        'فلانشة'    => 'flanged',
        'لحام'      => 'welded',
    ];

    /** @var array<string,list<string>> */
    private const MATERIALS = [
        'brass'           => ['brass', 'dzr'],
        'bronze'          => ['bronze'],
        'stainless steel' => ['stainless', 'ss304', 'ss316', 'aisi 304', 'aisi 316'],
        'carbon steel'    => ['carbon steel', 'mild steel'],
        'ductile iron'    => ['ductile iron', 'ductile'],
        'cast iron'       => ['cast iron'],
        'copper'          => ['copper'],
        'pvc'             => ['pvc', 'upvc'],
        'cpvc'            => ['cpvc'],
        'hdpe'            => ['hdpe'],
        'ppr'             => ['ppr'],
        'galvanized'      => ['galvanized', 'galvanised', ' gi '],
        'aluminium'       => ['aluminium', 'aluminum'],
    ];

    private function translateCommonTerms(string $text): string
    {
        $out = ' ' . $text . ' ';

        foreach (self::AR_TERMS as $ar => $en) {
            if (mb_strpos($out, $ar) !== false) {
                // Append rather than replace: the original wording still helps
                // keyword matching, and replacing can break compound phrases.
                $out .= ' ' . $en;
            }
        }

        return $out;
    }

    /**
     * A SKU in BOQ text looks like a part number: letters AND digits, usually
     * with a separator. Bare numbers are quantities or sizes, never SKUs.
     */
    private function extractSku(string $text): ?string
    {
        if (! preg_match_all('/\b([A-Z0-9]{2,}[-\/][A-Z0-9-\/]{2,})\b/i', $text, $m)) {
            return null;
        }

        foreach ($m[1] as $candidate) {
            $hasLetter = preg_match('/[a-zA-Z]/', $candidate) === 1;
            $hasDigit  = preg_match('/\d/', $candidate) === 1;

            // Skip things that are really dimensions ("2x4", "1/2").
            $isFraction = preg_match('#^\d+/\d+$#', $candidate) === 1;

            if ($hasLetter && $hasDigit && ! $isFraction && mb_strlen($candidate) >= 5) {
                return $candidate;
            }
        }

        return null;
    }

    /** Model tokens are alphanumeric runs that are not pure numbers or sizes. */
    private function extractModel(string $text): ?string
    {
        if (! preg_match_all('/\b([A-Z]{1,6}[-\s]?\d{2,6}[A-Z0-9-]*)\b/i', $text, $m)) {
            return null;
        }

        foreach ($m[1] as $candidate) {
            $clean = trim($candidate);
            // Exclude common non-model patterns (DN50, PN16, 2 inch).
            if (preg_match('/^(dn|pn|nb|od|id)\s*\d+$/i', $clean)) {
                continue;
            }
            if (mb_strlen($clean) >= 4) {
                return $clean;
            }
        }

        return null;
    }

    private function extractBrand(string $text): ?string
    {
        // Brands are matched against the catalog's own manufacturer list later;
        // here we only look for an explicit "brand: X" style hint.
        if (preg_match('/\b(?:brand|make|manufacturer|ماركة|صناعة)\s*[:\-]\s*([A-Za-z][\w\s&.-]{2,30})/iu', $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function extractMaterial(string $text): ?string
    {
        $haystack = ' ' . mb_strtolower($text) . ' ';

        foreach (self::MATERIALS as $canonical => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    return $canonical;
                }
            }
        }

        return null;
    }

    /**
     * Content words used for text similarity. Stop words and pure numbers are
     * dropped so "the 2 valve" does not match everything with a 2 in it.
     *
     * @return list<string>
     */
    private function keywords(string $text): array
    {
        $stop = ['and', 'the', 'for', 'with', 'from', 'per', 'each', 'type', 'size',
                 'complete', 'including', 'supply', 'install', 'installation', 'all',
                 'work', 'works', 'item', 'items', 'as', 'to', 'of', 'in', 'on'];

        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text)) ?: [];

        $keywords = [];
        foreach ($words as $w) {
            if (mb_strlen($w) < 3 || is_numeric($w) || in_array($w, $stop, true)) {
                continue;
            }
            $keywords[$w] = true;
        }

        return array_slice(array_keys($keywords), 0, 20);
    }
}
