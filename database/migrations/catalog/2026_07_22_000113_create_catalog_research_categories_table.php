<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nested categories for the research module. Named distinctly from the existing
 * pricing `catalog_categories` so both can coexist on the catalog connection.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('catalog_research_categories')) {
            return;
        }

        Schema::connection('catalog')->create('catalog_research_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('division_id')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index(); // nested
            $table->string('code')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['division_id', 'slug'], 'crc_division_slug_unique');
            $table->index(['division_id', 'parent_id'], 'crc_division_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_research_categories');
    }
};
