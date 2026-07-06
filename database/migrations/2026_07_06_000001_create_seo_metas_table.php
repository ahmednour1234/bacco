<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SEO metadata for public/landing pages, keyed by route name.
     * Every field is bilingual (*_en / *_ar) so admins can edit both locales.
     */
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Identity — which page this SEO record drives.
            $table->string('route_name')->unique();   // e.g. "landing.boq-pricing", "about"
            $table->string('label')->nullable();       // human-friendly name shown in admin

            // Core SEO
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->text('meta_desc_en')->nullable();
            $table->text('meta_desc_ar')->nullable();
            $table->string('keywords_en')->nullable();
            $table->string('keywords_ar')->nullable();

            // Open Graph
            $table->string('og_image')->nullable();
            $table->string('og_type')->nullable()->default('website');

            // Structured data (JSON-LD) — raw JSON string per locale, optional.
            $table->longText('schema_en')->nullable();
            $table->longText('schema_ar')->nullable();

            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
