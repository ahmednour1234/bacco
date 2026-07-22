<?php

namespace App\Models\Catalog\Research;

use App\Enums\Catalog\Research\AvailabilityStatusEnum;
use App\Enums\Catalog\Research\VerificationLevelEnum;
use App\Enums\Catalog\Research\VerificationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A real SKU / variant. Exactly one size, one connection, one SKU per row —
 * never a cartesian combination. `normalized_variant_key` is unique so research
 * writes are idempotent and duplicates are caught at the DB level.
 */
class ProductVariant extends Model
{
    use SoftDeletes;

    protected $connection = 'catalog';
    protected $table      = 'product_variants';

    protected $fillable = [
        'uuid', 'product_model_id', 'product_family_id', 'manufacturer_id',
        'manufacturer_sku', 'manufacturer_part_number', 'variant_name',
        'normalized_variant_key', 'size_id', 'connection_type_id',
        'connection_standard_id', 'pressure_rating_id', 'temperature_min',
        'temperature_max', 'temperature_unit', 'unit_id', 'operator_type_id',
        'finish_id', 'verification_level', 'verification_status',
        'availability_status', 'market_scope', 'technical_notes',
    ];

    protected $casts = [
        'temperature_min'     => 'decimal:2',
        'temperature_max'     => 'decimal:2',
        'verification_level'  => VerificationLevelEnum::class,
        'verification_status' => VerificationStatusEnum::class,
        'availability_status' => AvailabilityStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_model_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'size_id');
    }

    public function connectionType(): BelongsTo
    {
        return $this->belongsTo(ConnectionType::class, 'connection_type_id');
    }

    public function connectionStandard(): BelongsTo
    {
        return $this->belongsTo(ConnectionStandard::class, 'connection_standard_id');
    }

    public function pressureRating(): BelongsTo
    {
        return $this->belongsTo(PressureRating::class, 'pressure_rating_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function operatorType(): BelongsTo
    {
        return $this->belongsTo(OperationType::class, 'operator_type_id');
    }

    public function finish(): BelongsTo
    {
        return $this->belongsTo(Finish::class, 'finish_id');
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(
            Approval::class,
            'product_variant_approvals',
            'product_variant_id',
            'approval_id'
        )->withPivot([
            'certificate_number', 'valid_from', 'valid_to',
            'scope', 'source_id', 'verification_status',
        ])->withTimestamps();
    }

    public function standards(): BelongsToMany
    {
        return $this->belongsToMany(
            Standard::class,
            'product_variant_standards',
            'product_variant_id',
            'standard_id'
        )->withPivot(['source_id', 'notes'])->withTimestamps();
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ProductSourceEvidence::class, 'product_variant_id');
    }
}
