<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pivot: a model can have many materials, one per component (body, ball…). */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_model_materials')) {
            return;
        }

        Schema::connection('catalog')->create('product_model_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_model_id')->index();
            $table->unsignedBigInteger('material_id')->index();
            $table->string('component_type')->default('other')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_model_id', 'material_id', 'component_type'], 'pmm_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_model_materials');
    }
};
