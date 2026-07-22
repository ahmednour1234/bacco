<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogDivision extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_divisions';

    protected $fillable = ['code', 'name', 'slug', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function categories(): HasMany
    {
        return $this->hasMany(CatalogResearchCategory::class, 'division_id');
    }

    public function productFamilies(): HasMany
    {
        return $this->hasMany(ProductFamily::class, 'division_id');
    }
}
