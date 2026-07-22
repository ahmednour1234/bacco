<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Variant ↔ standard, each optionally backed by a source. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_variant_standards')) {
            return;
        }

        Schema::connection('catalog')->create('product_variant_standards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id')->index();
            $table->unsignedBigInteger('standard_id')->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_variant_id', 'standard_id'], 'pvs_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_variant_standards');
    }
};
