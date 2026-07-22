<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\VerificationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSourceEvidence extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'product_source_evidence';

    protected $fillable = [
        'source_document_id', 'product_model_id', 'product_variant_id',
        'field_name', 'extracted_value', 'source_excerpt', 'page_number',
        'verification_status', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'page_number'         => 'integer',
        'verified_at'         => 'datetime',
        'verification_status' => VerificationStatusEnum::class,
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceDocument::class, 'source_document_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_model_id');
    }
}
