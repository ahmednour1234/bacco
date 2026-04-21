<?php

namespace App\Models;

use App\Enums\BoqStatusEnum;
use App\Enums\BoqTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boq extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'boqs';

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status'     => BoqStatusEnum::class,
            'type'       => BoqTypeEnum::class,
            'deleted_at' => 'datetime',
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BoqItem::class);
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function quotationRequests(): HasMany
    {
        return $this->hasMany(QuotationRequest::class);
    }
}
