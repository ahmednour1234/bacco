<?php

namespace App\Models\Catalog\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Who sells a product — distinct from a manufacturer, who makes it.
 *
 * Several scraper sources can point at the same real merchant (the scraper DB
 * lists elburoj/KMCO/Zorins twice), so suppliers are keyed on a normalized
 * host derived from base_url. That merge is what stops one product picking up
 * two "different" prices that are really the same shop.
 */
class CatalogSupplier extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_suppliers';

    protected $fillable = [
        'uuid', 'name', 'normalized_name', 'slug', 'supplier_type', 'website',
        'country_code', 'city', 'contact_email', 'contact_phone',
        'scraper_source_id', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $supplier) {
            $supplier->uuid ??= (string) Str::uuid();
        });
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductVariantPrice::class, 'supplier_id');
    }

    /**
     * Identity key for a merchant: the bare host, lowercased, without "www."
     * and without any path or locale segment. https://elburoj.com/ar/... and
     * https://elburoj.com both collapse to "elburoj.com".
     */
    public static function normalizeHost(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        // parse_url needs a scheme to find a host; add one if it is missing.
        if (! Str::contains($url, '://')) {
            $url = 'https://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $host = strtolower($host);

        return (string) Str::of($host)->replaceMatches('/^www\./', '');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
