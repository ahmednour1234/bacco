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

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status'         => QuotationRequestStatusEnum::class,
            'source_type'    => QuotationSourceTypeEnum::class,
            'project_status' => QuotationProjectStatusEnum::class,
            'deleted_at'     => 'datetime',
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
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
