<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who quotes a price. Distinct from `manufacturers`: a manufacturer MAKES the
 * product, a supplier SELLS it. The same variant can be priced by many
 * suppliers (agent, distributor, retailer) at different tiers.
 *
 * A supplier may be linked back to a scraper source so scraped prices carry
 * their origin without hard-coding site names in the pricing tables.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('catalog_suppliers')) {
            return;
        }

        Schema::connection('catalog')->create('catalog_suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->string('normalized_name')->unique(); // dedup key
            $table->string('slug')->nullable()->index();

            // agent | distributor | retailer | manufacturer_direct | marketplace | unknown
            $table->string('supplier_type', 40)->default('unknown')->index();

            $table->string('website')->nullable();
            $table->string('country_code', 8)->nullable()->index();
            $table->string('city')->nullable();

            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 64)->nullable();

            // Which scraper source (qimta_ai DB) this supplier represents, if any.
            // Deliberately a plain integer: the scraper lives on another connection,
            // so a real FK is impossible — the link is resolved in code.
            $table->unsignedBigInteger('scraper_source_id')->nullable()->index();

            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_suppliers');
    }
};
