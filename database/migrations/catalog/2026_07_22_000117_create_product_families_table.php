<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A generic product definition — one Excel row becomes one Product Family
 * (e.g. "Ball Valve (Brass)"). Real variants are discovered later by research;
 * families themselves are NOT the cartesian expansion of their attributes.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_families')) {
            return;
        }

        Schema::connection('catalog')->create('product_families', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('source_code')->nullable()->index();
            $table->unsignedBigInteger('division_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('slug')->nullable()->index();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('default_unit_id')->nullable()->index();
            $table->string('research_status')->default('not_started')->index();
            $table->unsignedTinyInteger('research_priority')->default(5)->index();
            $table->string('research_scope')->default('saudi')->index();
            $table->string('target_market')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['research_status', 'research_priority'], 'pf_status_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_families');
    }
};
