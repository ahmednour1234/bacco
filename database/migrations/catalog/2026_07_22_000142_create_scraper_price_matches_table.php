<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a scraped product (qimta_ai.scraper_products) to a catalog variant.
 *
 * Matching is never silently trusted: every link records HOW it was made and
 * how confident we are. Only high-confidence matches auto-create a price; the
 * rest wait in review, mirroring the research pipeline's rule that nothing
 * unverified is presented as fact.
 *
 * match_method: sku | normalized_key | manufacturer_model | ai | manual
 * status:       pending | auto_accepted | confirmed | rejected
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('scraper_price_matches')) {
            return;
        }

        Schema::connection('catalog')->create('scraper_price_matches', function (Blueprint $table) {
            $table->id();

            // Cross-connection reference (scraper DB) — no FK by design.
            $table->unsignedBigInteger('scraper_product_id')->index();
            $table->unsignedBigInteger('scraper_source_id')->nullable()->index();

            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->unsignedBigInteger('product_family_id')->nullable()->index();

            $table->string('match_method', 32)->index();
            $table->decimal('confidence_score', 5, 2)->default(0); // 0..100
            $table->string('status', 24)->default('pending')->index();

            // Snapshot of what was matched, so review does not need the scraper DB.
            $table->string('scraped_name', 512)->nullable();
            $table->string('scraped_sku')->nullable()->index();
            $table->decimal('scraped_price', 14, 4)->nullable();
            $table->string('scraped_currency', 8)->nullable();
            $table->string('scraped_url', 2048)->nullable();

            $table->json('match_reasons')->nullable();  // why we think it matches
            $table->text('review_notes')->nullable();

            $table->unsignedBigInteger('price_id')->nullable()->index(); // created price, if any
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // One decision per (scraped product, variant) pair — re-running the
            // matcher updates rather than duplicates.
            $table->unique(['scraper_product_id', 'product_variant_id'], 'spm_scraped_variant_unique');
            $table->index(['status', 'confidence_score'], 'spm_status_confidence_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('scraper_price_matches');
    }
};
