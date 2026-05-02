<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class CatalogImportFailedRow extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_import_failed_rows';

    protected $fillable = [
        'catalog_import_id', 'row_number', 'row_data', 'error_message',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(CatalogImport::class, 'catalog_import_id');
    }
}
