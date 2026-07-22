<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\ImportRowStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogImportRow extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_import_rows';

    protected $fillable = [
        'catalog_import_id', 'sheet_name', 'excel_row_number', 'source_code',
        'division_raw', 'category_raw', 'item_description_raw', 'material_raw',
        'manufacturer_raw', 'connection_raw', 'size_raw', 'pressure_raw',
        'standard_raw', 'approval_raw', 'unit_raw',
        'original_row', 'normalized_row', 'row_hash',
        'import_status', 'error_message', 'product_family_id',
    ];

    protected $casts = [
        'original_row'   => 'array',
        'normalized_row' => 'array',
        'import_status'  => ImportRowStatusEnum::class,
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(CatalogImport::class, 'catalog_import_id');
    }

    public function productFamily(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }
}
