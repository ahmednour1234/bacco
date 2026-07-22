<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\ManufacturerTypeEnum;
use App\Enums\Catalog\Research\VerificationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Manufacturer extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'manufacturers';

    protected $fillable = [
        'uuid', 'name', 'normalized_name', 'slug', 'official_website',
        'official_domain', 'country_id', 'manufacturer_type', 'market_region',
        'is_active', 'verification_status',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'manufacturer_type'   => ManufacturerTypeEnum::class,
        'verification_status' => VerificationStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function families(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductFamily::class,
            'product_family_manufacturers',
            'manufacturer_id',
            'product_family_id'
        )->withPivot(['source_type', 'priority', 'research_enabled'])->withTimestamps();
    }

    public function series(): HasMany
    {
        return $this->hasMany(ProductSeries::class, 'manufacturer_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'manufacturer_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'manufacturer_id');
    }
}
