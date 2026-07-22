<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ResearchJob extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'research_jobs';

    protected $fillable = [
        'uuid', 'product_family_id', 'manufacturer_id', 'job_type', 'provider',
        'model_name', 'research_query', 'input_payload', 'status', 'priority',
        'attempts', 'max_attempts', 'started_at', 'completed_at', 'failed_at',
        'error_message', 'created_by',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'priority'      => 'integer',
        'attempts'      => 'integer',
        'max_attempts'  => 'integer',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'failed_at'     => 'datetime',
        'job_type'      => ResearchJobTypeEnum::class,
        'status'        => ResearchJobStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ResearchJobResult::class, 'research_job_id');
    }
}
