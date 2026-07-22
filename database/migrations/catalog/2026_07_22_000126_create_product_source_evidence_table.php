<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ties a specific field value on a model or variant to the exact source it came
 * from, with the excerpt. This is what makes a variant verifiable.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_source_evidence')) {
            return;
        }

        Schema::connection('catalog')->create('product_source_evidence', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_document_id')->index();
            $table->unsignedBigInteger('product_model_id')->nullable()->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->string('field_name')->index();
            $table->text('extracted_value')->nullable();
            $table->text('source_excerpt')->nullable();
            $table->unsignedInteger('page_number')->nullable();
            $table->string('verification_status')->default('pending')->index();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['product_variant_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_source_evidence');
    }
};
