<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The base model before size variation. Materials attach via a pivot. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_models')) {
            return;
        }

        Schema::connection('catalog')->create('product_models', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('product_series_id')->index();
            $table->unsignedBigInteger('manufacturer_id')->index();
            $table->unsignedBigInteger('product_family_id')->index();
            $table->string('model_number')->nullable()->index();
            $table->string('manufacturer_model_code')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->unsignedBigInteger('body_material_id')->nullable()->index();
            $table->unsignedBigInteger('ball_material_id')->nullable()->index();
            $table->unsignedBigInteger('seat_material_id')->nullable()->index();
            $table->unsignedBigInteger('port_type_id')->nullable()->index();
            $table->unsignedSmallInteger('pieces_count')->nullable();
            $table->unsignedBigInteger('operation_type_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('lifecycle_status')->default('unknown')->index();
            $table->string('verification_status')->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_models');
    }
};
