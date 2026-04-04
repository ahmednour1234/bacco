<?php

namespace App\Models;

use App\Enums\QuotationItemStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationItem extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'quantity'             => 'decimal:3',
            'status'               => QuotationItemStatusEnum::class,
            'engineering_required' => 'boolean',
            'is_selected'          => 'boolean',
            'confidence'           => 'decimal:2',
            'raw_data'             => 'array',
            'ai_extracted'         => 'boolean',
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function quotationRequest(): BelongsTo
    {
        return $this->belongsTo(QuotationRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function quotationVersionItems(): HasMany
    {
        return $this->hasMany(QuotationVersionItem::class);
    }
}
