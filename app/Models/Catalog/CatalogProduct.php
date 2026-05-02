<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CatalogProduct extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'catalog_products';

    protected $fillable = [
        'uuid', 'catalog_id', 'category_id', 'qimta_code', 'division',
        'item_description', 'sub_type', 'product_name', 'type_of_material',
        'size', 'unit', 'lead_time', 'source_file', 'import_batch_id',
        'status', 'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(fn(self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function catalog()
    {
        return $this->belongsTo(Catalog::class, 'catalog_id');
    }

    public function category()
    {
        return $this->belongsTo(CatalogCategory::class, 'category_id');
    }

    public function importBatch()
    {
        return $this->belongsTo(CatalogImport::class, 'import_batch_id');
    }
}
