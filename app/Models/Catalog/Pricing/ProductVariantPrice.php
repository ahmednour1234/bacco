<?php

namespace App\Models\Catalog\Pricing;

use App\Enums\Catalog\Pricing\PriceConfidenceEnum;
use App\Enums\Catalog\Pricing\PriceSourceEnum;
use App\Enums\Catalog\Pricing\PriceTierEnum;
use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * One price, for one variant, from one supplier, at one tier and quantity band.
 *
 * Never collapsed onto product_variants: a real product carries several live
 * prices at once (retail vs wholesale vs bulk, per supplier). Prices churn;
 * the product does not.
 */
class ProductVariantPrice extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'product_variant_prices';

    protected $fillable = [
        'uuid', 'product_variant_id', 'supplier_id', 'price_tier', 'price',
        'currency', 'min_quantity', 'max_quantity', 'price_unit', 'source',
        'source_url', 'scraper_product_id', 'scraper_source_id', 'confidence',
        'valid_from', 'valid_to', 'lead_time_days', 'is_active', 'captured_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'price'        => 'decimal:4',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'is_active'    => 'boolean',
        'valid_from'   => 'date',
        'valid_to'     => 'date',
        'captured_at'  => 'datetime',
        'price_tier'   => PriceTierEnum::class,
        'source'       => PriceSourceEnum::class,
        'confidence'   => PriceConfidenceEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $price) {
            $price->uuid ??= (string) Str::uuid();
        });
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(CatalogSupplier::class, 'supplier_id');
    }

    /**
     * Prices that may back a customer quotation: active, in date, from a
     * source strong enough to quote, and not merely an estimate.
     */
    public function scopeQuotable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereIn('source', [
                PriceSourceEnum::SupplierQuote->value,
                PriceSourceEnum::Manual->value,
                PriceSourceEnum::CatalogPdf->value,
            ])
            ->whereIn('confidence', [
                PriceConfidenceEnum::Verified->value,
                PriceConfidenceEnum::Unverified->value,
            ])
            ->where(fn ($q) => $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', now()));
    }

    public function scopeTier(Builder $query, PriceTierEnum|string $tier): Builder
    {
        return $query->where('price_tier', $tier instanceof PriceTierEnum ? $tier->value : $tier);
    }

    /** Prices valid for a given order quantity (respects MOQ bands). */
    public function scopeForQuantity(Builder $query, int $qty): Builder
    {
        return $query
            ->where('min_quantity', '<=', $qty)
            ->where(fn ($q) => $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $qty));
    }

    /** A price older than the configured window is no longer trustworthy. */
    public function isStale(?int $days = null): bool
    {
        $days = $days ?? (int) config('catalog_research.pricing.stale_after_days', 90);
        $seen = $this->captured_at ?? $this->created_at;

        return $seen !== null && $seen->diffInDays(now()) > $days;
    }

    /** Estimates must be labelled wherever a human can see them. */
    public function needsEstimateWarning(): bool
    {
        return $this->source instanceof PriceSourceEnum
            && $this->source->requiresEstimateWarning();
    }
}
