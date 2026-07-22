<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every fact must trace to a source. Official manufacturer sources are the
 * primary reference; search results are for discovery only.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('source_documents')) {
            return;
        }

        Schema::connection('catalog')->create('source_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
            $table->unsignedBigInteger('product_family_id')->nullable()->index();
            $table->unsignedBigInteger('product_series_id')->nullable()->index();
            $table->string('source_type')->default('other')->index();
            $table->string('title')->nullable();
            $table->text('source_url')->nullable();
            $table->string('domain')->nullable()->index();
            $table->string('file_path')->nullable();
            $table->date('publication_date')->nullable();
            $table->timestamp('checked_at')->nullable()->index();
            $table->boolean('is_official')->default(false)->index();
            $table->string('source_status')->default('active')->index();
            $table->string('content_hash', 64)->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('source_documents');
    }
};
