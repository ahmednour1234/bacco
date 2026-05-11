<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CatalogStats
{
    /**
     * Cache TTL in seconds (6 hours — balances freshness vs DB load).
     */
    private const TTL = 21600;

    /**
     * Fallback values used when the catalog DB is unreachable.
     * Last verified against production DB (May 2026).
     */
    private const FALLBACK = [
        'products'   => 13190,
        'items'      => 1334,
        'categories' => 72,
        'divisions'  => 5,
        'brands'     => 72,
    ];

    /**
     * Return stats array. Cached for 6 hours, falls back to constants if DB unavailable.
     */
    public static function get(): array
    {
        return Cache::remember('catalog_stats', self::TTL, function () {
            try {
                $db = DB::connection('catalog');

                return [
                    'products'   => $db->table('catalog_products')->count(),
                    'items'      => $db->table('catalog_products')
                        ->whereNotNull('item_description')
                        ->where('item_description', '!=', '')
                        ->distinct()->count('item_description'),
                    'categories' => $db->table('catalog_categories')->count(),
                    'divisions'  => $db->table('catalog_products')
                        ->whereNotNull('division')
                        ->where('division', '!=', '')
                        ->distinct()->count('division'),
                    'brands'     => $db->table('catalog_products')
                        ->whereNotNull('brand')
                        ->where('brand', '!=', '')
                        ->distinct()->count('brand'),
                ];
            } catch (\Exception $e) {
                Log::warning('CatalogStats: DB unavailable, using fallback', ['err' => $e->getMessage()]);
                return self::FALLBACK;
            }
        });
    }

    /**
     * Format a number for display (adds commas).
     */
    public static function format(int $n): string
    {
        return number_format($n);
    }

    /**
     * Invalidate the cache (call after a catalog import).
     */
    public static function flush(): void
    {
        Cache::forget('catalog_stats');
    }
}
