<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductSeries extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'product_series';

    protected $fillable = [
        'uuid', 'manufacturer_id', 'product_family_id', 'series_name',
        'model_number', 'normalized_model_number', 'official_product_name',
        'description', 'series_status', 'official_page_url',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'product_series_id');
    }
}
