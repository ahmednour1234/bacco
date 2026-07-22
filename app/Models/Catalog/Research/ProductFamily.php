<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\ResearchScopeEnum;
use App\Enums\Catalog\Research\ResearchStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A generic product definition (one Excel row). Real variants are discovered by
 * research — a family is never the cartesian expansion of its own attributes.
 */
class ProductFamily extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'product_families';

    protected $fillable = [
        'uuid', 'source_code', 'division_id', 'category_id', 'name',
        'normalized_name', 'slug', 'description', 'default_unit_id',
        'research_status', 'research_priority', 'research_scope',
        'target_market', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'research_priority' => 'integer',
        'research_status'   => ResearchStatusEnum::class,
        'research_scope'    => ResearchScopeEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(CatalogDivision::class, 'division_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogResearchCategory::class, 'category_id');
    }

    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }

    public function manufacturers(): BelongsToMany
    {
        return $this->belongsToMany(
            Manufacturer::class,
            'product_family_manufacturers',
            'product_family_id',
            'manufacturer_id'
        )->withPivot(['source_type', 'priority', 'research_enabled'])->withTimestamps();
    }

    public function sourceRows(): HasMany
    {
        return $this->hasMany(CatalogImportRow::class, 'product_family_id');
    }

    public function series(): HasMany
    {
        return $this->hasMany(ProductSeries::class, 'product_family_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'product_family_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_family_id');
    }

    public function researchJobs(): HasMany
    {
        return $this->hasMany(ResearchJob::class, 'product_family_id');
    }
}
