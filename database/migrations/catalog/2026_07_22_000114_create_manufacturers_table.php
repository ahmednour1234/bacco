<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Manufacturers (NIBCO, KITZ, Victaulic…). No pricing. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('manufacturers')) {
            return;
        }

        Schema::connection('catalog')->create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('normalized_name')->unique(); // dedup key
            $table->string('slug')->nullable()->index();
            $table->string('official_website')->nullable();
            $table->string('official_domain')->nullable()->index(); // to validate source URLs
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->string('manufacturer_type')->default('unknown')->index();
            $table->string('market_region')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('verification_status')->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('manufacturers');
    }
};
