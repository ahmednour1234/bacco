<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\SourceStatusEnum;
use App\Enums\Catalog\Research\SourceTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SourceDocument extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'source_documents';

    protected $fillable = [
        'uuid', 'manufacturer_id', 'product_family_id', 'product_series_id',
        'source_type', 'title', 'source_url', 'domain', 'file_path',
        'publication_date', 'checked_at', 'is_official', 'source_status',
        'content_hash', 'notes',
    ];

    protected $casts = [
        'is_official'      => 'boolean',
        'publication_date' => 'date',
        'checked_at'       => 'datetime',
        'source_type'      => SourceTypeEnum::class,
        'source_status'    => SourceStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ProductSourceEvidence::class, 'source_document_id');
    }
}
