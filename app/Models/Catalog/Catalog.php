<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Catalog extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'catalogs';

    protected $fillable = ['uuid', 'name', 'slug', 'description', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
            $model->slug ??= Str::slug($model->name);
        });
    }

    public function categories()
    {
        return $this->hasMany(CatalogCategory::class, 'catalog_id');
    }

    public function products()
    {
        return $this->hasMany(CatalogProduct::class, 'catalog_id');
    }

    public function imports()
    {
        return $this->hasMany(CatalogImport::class, 'catalog_id');
    }
}
