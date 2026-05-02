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
        'uuid', 'catalog_id', 'parent_id', 'name', 'slug', 'description', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
            $model->slug ??= Str::slug($model->name);
        });
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
