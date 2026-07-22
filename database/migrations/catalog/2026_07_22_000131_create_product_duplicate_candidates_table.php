<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suspected-duplicate variant pairs. Variants differing by SKU are never
 * auto-merged — they land here for human review.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_duplicate_candidates')) {
            return;
        }

        Schema::connection('catalog')->create('product_duplicate_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('first_product_variant_id')->index();
            $table->unsignedBigInteger('second_product_variant_id')->index();
            $table->decimal('similarity_score', 5, 4)->default(0)->index();
            $table->json('match_reasons')->nullable();
            $table->string('status')->default('open')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['first_product_variant_id', 'second_product_variant_id'],
                'pdc_pair_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_duplicate_candidates');
    }
};
