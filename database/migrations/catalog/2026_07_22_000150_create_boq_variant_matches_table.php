<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a BOQ line to the real catalog product(s) that satisfy it.
 *
 * This is the bridge that makes the catalog usable: a BOQ says "brass ball
 * valve 2 inch", which cannot be priced, while a variant is a specific SKU that
 * can. One BOQ item may match several variants (different manufacturers), so
 * matches are ranked and the chosen one is marked.
 *
 * Lives on the catalog connection while boq_items lives on the main database,
 * so boq_item_id is a plain integer — the link is resolved in code.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('boq_variant_matches')) {
            return;
        }

        Schema::connection('catalog')->create('boq_variant_matches', function (Blueprint $table) {
            $table->id();

            // Cross-connection reference to the main DB — no FK by design.
            $table->unsignedBigInteger('boq_id')->index();
            $table->unsignedBigInteger('boq_item_id')->index();

            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->unsignedBigInteger('product_family_id')->nullable()->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();

            // exact_sku | brand_model | spec_match | family_only | manual
            $table->string('match_method', 32)->index();
            $table->decimal('confidence_score', 5, 2)->default(0); // 0..100
            $table->unsignedSmallInteger('rank')->default(1);      // 1 = best

            // pending | auto_selected | confirmed | rejected
            $table->string('status', 24)->default('pending')->index();
            $table->boolean('is_selected')->default(false)->index();

            // What the parser understood from the BOQ text, kept so a reviewer
            // can see WHY these two were linked without re-running the parser.
            $table->json('parsed_specs')->nullable();
            $table->json('match_reasons')->nullable();
            $table->json('spec_conflicts')->nullable(); // where they disagree

            // Price snapshot at match time; the live price may move later.
            $table->unsignedBigInteger('price_id')->nullable()->index();
            $table->decimal('unit_price', 14, 4)->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('price_tier', 24)->nullable();
            $table->string('price_source', 32)->nullable();

            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // One row per (item, variant) so re-matching updates in place.
            $table->unique(['boq_item_id', 'product_variant_id'], 'bvm_item_variant_unique');
            $table->index(['boq_id', 'status'], 'bvm_boq_status_idx');
            $table->index(['boq_item_id', 'rank'], 'bvm_item_rank_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('boq_variant_matches');
    }
};
