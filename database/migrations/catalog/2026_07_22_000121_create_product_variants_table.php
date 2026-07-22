<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The most important table. Each row is a real SKU / variant — never a
 * cartesian combination. One variant holds exactly ONE size, ONE connection,
 * ONE SKU. `normalized_variant_key` is unique for idempotent research writes
 * and duplicate detection.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_variants')) {
            return;
        }

        Schema::connection('catalog')->create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('product_model_id')->index();
            $table->unsignedBigInteger('product_family_id')->index();
            $table->unsignedBigInteger('manufacturer_id')->index();

            $table->string('manufacturer_sku')->nullable()->index();
            $table->string('manufacturer_part_number')->nullable()->index();
            $table->string('variant_name')->nullable();
            $table->string('normalized_variant_key')->unique(); // idempotency + dedup

            $table->unsignedBigInteger('size_id')->nullable()->index();
            $table->unsignedBigInteger('connection_type_id')->nullable()->index();
            $table->unsignedBigInteger('connection_standard_id')->nullable()->index();
            $table->unsignedBigInteger('pressure_rating_id')->nullable()->index();
            $table->decimal('temperature_min', 8, 2)->nullable();
            $table->decimal('temperature_max', 8, 2)->nullable();
            $table->string('temperature_unit', 4)->nullable();
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->unsignedBigInteger('operator_type_id')->nullable()->index();
            $table->unsignedBigInteger('finish_id')->nullable()->index();

            $table->string('verification_level')->default('ai_discovered_unverified')->index();
            $table->string('verification_status')->default('pending')->index();
            $table->string('availability_status')->default('unknown')->index();
            $table->string('market_scope')->nullable()->index();
            $table->text('technical_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manufacturer_id', 'verification_status'], 'pv_manufacturer_vstatus_idx');
            $table->index(['product_family_id', 'verification_level'], 'pv_family_vlevel_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_variants');
    }
};
