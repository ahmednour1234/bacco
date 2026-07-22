<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogResearchCategory extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_research_categories';

    protected $fillable = [
        'division_id', 'parent_id', 'code', 'name',
        'slug', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(CatalogDivision::class, 'division_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
