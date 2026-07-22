<?php

namespace App\Models\Catalog\Pricing;

use App\Enums\Catalog\Pricing\BoqMatchMethodEnum;
use App\Enums\Catalog\Pricing\MatchStatusEnum;
use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A candidate catalog product for one BOQ line, with the price it would carry.
 *
 * Several candidates normally exist per line (different manufacturers); `rank`
 * orders them and `is_selected` marks the one that feeds the quotation.
 */
class BoqVariantMatch extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'boq_variant_matches';

    protected $fillable = [
        'boq_id', 'boq_item_id', 'product_variant_id', 'product_family_id',
        'manufacturer_id', 'match_method', 'confidence_score', 'rank', 'status',
        'is_selected', 'parsed_specs', 'match_reasons', 'spec_conflicts',
        'price_id', 'unit_price', 'currency', 'price_tier', 'price_source',
        'review_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'unit_price'       => 'decimal:4',
        'rank'             => 'integer',
        'is_selected'      => 'boolean',
        'parsed_specs'     => 'array',
        'match_reasons'    => 'array',
        'spec_conflicts'   => 'array',
        'reviewed_at'      => 'datetime',
        'match_method'     => BoqMatchMethodEnum::class,
        'status'           => MatchStatusEnum::class,
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(ProductVariantPrice::class, 'price_id');
    }

    public function scopeForBoq(Builder $query, int $boqId): Builder
    {
        return $query->where('boq_id', $boqId);
    }

    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }

    public function scopeBest(Builder $query): Builder
    {
        return $query->where('rank', 1);
    }

    /** A match is only usable in a quotation once it carries a real price. */
    public function isPriceable(): bool
    {
        return $this->product_variant_id !== null
            && $this->unit_price !== null
            && (float) $this->unit_price > 0;
    }
}
