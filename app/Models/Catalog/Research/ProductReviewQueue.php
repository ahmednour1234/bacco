<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\ReviewSeverityEnum;
use App\Enums\Catalog\Research\ReviewStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductReviewQueue extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'product_review_queue';

    protected $fillable = [
        'reviewable_type', 'reviewable_id', 'reason', 'severity',
        'current_data', 'suggested_data', 'status', 'assigned_to',
        'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'current_data'   => 'array',
        'suggested_data' => 'array',
        'reviewed_at'    => 'datetime',
        'severity'       => ReviewSeverityEnum::class,
        'status'         => ReviewStatusEnum::class,
    ];

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
}
