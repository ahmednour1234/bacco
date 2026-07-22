<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A manufacturer's series / model family (e.g. NIBCO KT-585-70-UL). */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_series')) {
            return;
        }

        Schema::connection('catalog')->create('product_series', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('manufacturer_id')->index();
            $table->unsignedBigInteger('product_family_id')->index();
            $table->string('series_name');
            $table->string('model_number')->nullable()->index();
            $table->string('normalized_model_number')->nullable()->index();
            $table->string('official_product_name')->nullable();
            $table->text('description')->nullable();
            $table->string('series_status')->default('unknown')->index();
            $table->string('official_page_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manufacturer_id', 'product_family_id'], 'ps_manufacturer_family_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_series');
    }
};
