<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pivot: which manufacturers are linked to a family, and how they got there. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_family_manufacturers')) {
            return;
        }

        Schema::connection('catalog')->create('product_family_manufacturers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_family_id')->index();
            $table->unsignedBigInteger('manufacturer_id')->index();
            $table->string('source_type')->default('imported_from_excel')->index();
            $table->unsignedTinyInteger('priority')->default(5);
            $table->boolean('research_enabled')->default(true)->index();
            $table->timestamps();

            $table->unique(['product_family_id', 'manufacturer_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_family_manufacturers');
    }
};
