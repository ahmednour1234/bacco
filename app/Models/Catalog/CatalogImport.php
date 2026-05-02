<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CatalogImport extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_imports';

    protected $fillable = [
        'uuid', 'file_name', 'file_path', 'catalog_id', 'status',
        'total_rows', 'processed_rows', 'inserted_rows', 'updated_rows',
        'failed_rows', 'error_message', 'started_at', 'finished_at', 'uploaded_by',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn(self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function catalog()
    {
        return $this->belongsTo(Catalog::class, 'catalog_id');
    }

    public function failedRows()
    {
        return $this->hasMany(CatalogImportFailedRow::class, 'catalog_import_id');
    }

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isProcessing(): bool{ return $this->status === 'processing'; }

    public function progressPercent(): int
    {
        if ($this->total_rows <= 0) return 0;
        return min(100, (int) round(($this->processed_rows / $this->total_rows) * 100));
    }
}
