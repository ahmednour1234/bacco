<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * THE pricing table. One row = one price, for one variant, from one supplier,
 * at one tier, for one quantity band, valid over one period.
 *
 * Deliberately NOT a column on product_variants: the same real product carries
 * many prices at once (retail vs wholesale vs bulk, different suppliers,
 * different dates). Prices change constantly; the product does not.
 *
 * Tiers answer the "الجملة والقطاعي والجملة بكميات كبيرة" requirement:
 *   list      — manufacturer list price / MSRP (reference)
 *   retail    — single-unit consumer price
 *   wholesale — trade price (usually needs a MOQ)
 *   bulk      — large-quantity price (higher MOQ, lower unit price)
 *   project   — negotiated project-specific price
 *
 * `source` records HOW we learned the price, which drives how much it can be
 * trusted: a scraped or AI-estimated price must never be presented as a firm
 * supplier quote.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_variant_prices')) {
            return;
        }

        Schema::connection('catalog')->create('product_variant_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('product_variant_id')->index();
            $table->unsignedBigInteger('supplier_id')->nullable()->index();

            // list | retail | wholesale | bulk | project
            $table->string('price_tier', 24)->default('retail')->index();

            $table->decimal('price', 14, 4);
            $table->string('currency', 8)->default('SAR')->index();

            // Quantity band this price applies to. bulk tiers carry a high min.
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('max_quantity')->nullable();
            $table->string('price_unit', 40)->nullable(); // each, meter, roll, box...

            // manual | supplier_quote | scraped | ai_estimate | catalog_pdf
            $table->string('source', 32)->default('manual')->index();
            $table->string('source_url', 2048)->nullable();

            // Origin in the scraper DB, when this price came from a scrape.
            // Plain integers: cross-connection, so no FK (resolved in code).
            $table->unsignedBigInteger('scraper_product_id')->nullable()->index();
            $table->unsignedBigInteger('scraper_source_id')->nullable()->index();

            // How much we trust it: verified | unverified | estimated | stale
            $table->string('confidence', 24)->default('unverified')->index();

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->unsignedSmallInteger('lead_time_days')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('captured_at')->nullable(); // when the price was observed
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // One live price per (variant, supplier, tier, quantity band) — makes
            // re-scraping idempotent instead of piling up duplicate rows.
            $table->unique(
                ['product_variant_id', 'supplier_id', 'price_tier', 'min_quantity', 'currency'],
                'pvp_variant_supplier_tier_unique'
            );

            $table->index(['product_variant_id', 'price_tier', 'is_active'], 'pvp_variant_tier_active_idx');
            $table->index(['source', 'confidence'], 'pvp_source_confidence_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_variant_prices');
    }
};
