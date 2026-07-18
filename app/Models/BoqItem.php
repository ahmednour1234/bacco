<?php

namespace App\Models;

use App\Enums\QuotationItemStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqItem extends BaseModel
{
    use HasFactory;

    protected $table = 'boq_items';

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'quantity'             => 'decimal:3',
            'unit_price'           => 'decimal:2',
            'status'               => QuotationItemStatusEnum::class,
            'engineering_required' => 'boolean',
            'is_selected'          => 'boolean',
            'confidence'           => 'decimal:2',
            'raw_data'             => 'array',
            'ai_extracted'         => 'boolean',
            'missing_specs'        => 'array',
            'spec_answers'         => 'array',
            'validated_at'         => 'datetime',
            // Product Specification & Pricing Qualification Engine
            'supplyable'                 => 'boolean',
            'confirmed_specifications'   => 'array',
            'inferred_specifications'    => 'array',
            'assumptions'                => 'array',
            'quantity_warnings'          => 'array',
            'unit_warnings'              => 'array',
            'compatibility_warnings'     => 'array',
            'confidence_score'           => 'integer',
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
