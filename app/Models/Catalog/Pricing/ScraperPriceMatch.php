<?php

namespace App\Models\Catalog\Pricing;

use App\Enums\Catalog\Pricing\MatchMethodEnum;
use App\Enums\Catalog\Pricing\MatchStatusEnum;
use App\Models\Catalog\Research\ProductFamily;
use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A proposed link between a scraped product and a catalog variant.
 *
 * The scraped side lives on another connection, so it is referenced by plain
 * id plus a snapshot of the fields a reviewer needs — review must not depend
 * on the scraper DB being reachable.
 */
class ScraperPriceMatch extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'scraper_price_matches';

    protected $fillable = [
        'scraper_product_id', 'scraper_source_id', 'product_variant_id',
        'product_family_id', 'match_method', 'confidence_score', 'status',
        'scraped_name', 'scraped_sku', 'scraped_price', 'scraped_currency',
        'scraped_url', 'match_reasons', 'review_notes', 'price_id',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'scraped_price'    => 'decimal:4',
        'match_reasons'    => 'array',
        'reviewed_at'      => 'datetime',
        'match_method'     => MatchMethodEnum::class,
        'status'           => MatchStatusEnum::class,
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(ProductVariantPrice::class, 'price_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', MatchStatusEnum::Pending->value);
    }

    /** Links whose price is allowed to exist in the catalog. */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereIn('status', [
            MatchStatusEnum::AutoAccepted->value,
            MatchStatusEnum::Confirmed->value,
        ]);
    }
}
