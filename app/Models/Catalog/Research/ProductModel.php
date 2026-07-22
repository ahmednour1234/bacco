<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\VerificationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductModel extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'product_models';

    protected $fillable = [
        'uuid', 'product_series_id', 'manufacturer_id', 'product_family_id',
        'model_number', 'manufacturer_model_code', 'product_name',
        'body_material_id', 'ball_material_id', 'seat_material_id',
        'port_type_id', 'pieces_count', 'operation_type_id', 'description',
        'lifecycle_status', 'verification_status',
    ];

    protected $casts = [
        'pieces_count'        => 'integer',
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

    public function series(): BelongsTo
    {
        return $this->belongsTo(ProductSeries::class, 'product_series_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    public function bodyMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'body_material_id');
    }

    public function ballMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'ball_material_id');
    }

    public function seatMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'seat_material_id');
    }

    public function portType(): BelongsTo
    {
        return $this->belongsTo(PortType::class, 'port_type_id');
    }

    public function operationType(): BelongsTo
    {
        return $this->belongsTo(OperationType::class, 'operation_type_id');
    }

    /** All materials (multi-component) via pivot. */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(
            Material::class,
            'product_model_materials',
            'product_model_id',
            'material_id'
        )->withPivot(['component_type', 'notes'])->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_model_id');
    }
}
