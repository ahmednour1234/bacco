<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\CatalogImportStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Research-module Excel import. Distinct from the pricing CatalogImport
 * (App\Models\Catalog\CatalogImport) — this one uses table
 * `research_catalog_imports` and never touches prices.
 */
class CatalogImport extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'research_catalog_imports';

    protected $fillable = [
        'uuid', 'original_file_name', 'stored_file_path', 'file_type', 'file_size',
        'sheets_count', 'total_rows', 'imported_rows', 'duplicate_rows', 'failed_rows',
        'status', 'error_message', 'column_mapping', 'started_at', 'completed_at', 'uploaded_by',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'status'         => CatalogImportStatusEnum::class,
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function rows(): HasMany
    {
        return $this->hasMany(CatalogImportRow::class, 'catalog_import_id');
    }

    public function progressPercent(): int
    {
        if ($this->total_rows <= 0) {
            return 0;
        }

        $done = $this->imported_rows + $this->duplicate_rows + $this->failed_rows;

        return min(100, (int) round(($done / $this->total_rows) * 100));
    }
}
