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
        'uuid', 'catalog_id', 'category_id', 'qimta_code',
        'division', 'division_ar',
        'item_description', 'item_description_ar',
        'sub_type', 'sub_type_ar',
        'product_name', 'product_name_ar',
        'type_of_material',
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

    // ── Locale-aware accessors ───────────────────────────────────────────────
    // Return the value for the current app locale, cross-falling back to the
    // other language so a field is never blank when only one language exists.

    private function localized(?string $en, ?string $ar): string
    {
        $isAr = app()->getLocale() === 'ar';
        $primary  = $isAr ? $ar : $en;
        $fallback = $isAr ? $en : $ar;

        return trim((string) ($primary !== null && $primary !== '' ? $primary : $fallback));
    }

    public function getDivisionLabelAttribute(): string
    {
        return $this->localized($this->division, $this->division_ar);
    }

    public function getProductNameLabelAttribute(): string
    {
        return $this->localized($this->product_name, $this->product_name_ar);
    }

    public function getItemDescriptionLabelAttribute(): string
    {
        return $this->localized($this->item_description, $this->item_description_ar);
    }

    public function getSubTypeLabelAttribute(): string
    {
        return $this->localized($this->sub_type, $this->sub_type_ar);
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
