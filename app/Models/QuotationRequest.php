<?php

namespace App\Models;

use App\Enums\QuotationProjectStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationRequest extends BaseModel
{
    use HasFactory, SoftDeletes;

    /** Number of days before a quotation's prices are considered stale. */
    const EXPIRY_DAYS = 10;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status'            => QuotationRequestStatusEnum::class,
            'source_type'       => QuotationSourceTypeEnum::class,
            'project_status'    => QuotationProjectStatusEnum::class,
            'prices_fetched_at' => 'datetime',
            'deleted_at'        => 'datetime',
        ]);
    }

    /**
     * A quotation expires after EXPIRY_DAYS days from the last time prices
     * were fetched (or from creation if prices have never been re-fetched).
     * Only editable quotations (draft / tender) can expire.
     */
    public function isExpired(): bool
    {
        if (! in_array($this->status->value, ['tender', 'draft'], true)) {
            return false;
        }

        $reference = $this->prices_fetched_at ?? $this->created_at;

        return $reference->lt(now()->subDays(self::EXPIRY_DAYS));
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class);
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuotationVersion::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(QuotationComment::class);
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
