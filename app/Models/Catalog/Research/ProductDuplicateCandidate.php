<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\ReviewStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDuplicateCandidate extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'product_duplicate_candidates';

    protected $fillable = [
        'first_product_variant_id', 'second_product_variant_id',
        'similarity_score', 'match_reasons', 'status',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'similarity_score' => 'decimal:4',
        'match_reasons'    => 'array',
        'reviewed_at'      => 'datetime',
        'status'           => ReviewStatusEnum::class,
    ];

    public function first(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'first_product_variant_id');
    }

    public function second(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'second_product_variant_id');
    }
}
