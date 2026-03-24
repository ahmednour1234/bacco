<?php

namespace App\Models;

use App\Enums\ProjectStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status'              => ProjectStatusEnum::class,
            'start_date'          => 'date',
            'expected_end_date'   => 'date',
            'actual_end_date'     => 'date',
            'deleted_at'          => 'datetime',
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function engineeringUpdates(): HasMany
    {
        return $this->hasMany(EngineeringUpdate::class);
    }

    public function logisticsUpdates(): HasMany
    {
        return $this->hasMany(LogisticsUpdate::class);
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }
}
