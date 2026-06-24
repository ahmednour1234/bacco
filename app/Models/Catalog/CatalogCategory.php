<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CatalogCategory extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'catalog_categories';

    protected $fillable = [
        'uuid', 'catalog_id', 'parent_id', 'name', 'name_ar', 'slug', 'description', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
            $model->slug ??= Str::slug($model->name);
        });
    }

    /**
     * Category label for the current locale, cross-falling back to the other
     * language so it is never blank.
     */
    public function getNameLabelAttribute(): string
    {
        $isAr     = app()->getLocale() === 'ar';
        $primary  = $isAr ? $this->name_ar : $this->name;
        $fallback = $isAr ? $this->name : $this->name_ar;

        return trim((string) ($primary !== null && $primary !== '' ? $primary : $fallback));
    }

    public function catalog()
    {
        return $this->belongsTo(Catalog::class, 'catalog_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(CatalogProduct::class, 'category_id');
    }
}
