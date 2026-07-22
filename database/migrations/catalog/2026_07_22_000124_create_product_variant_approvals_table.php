<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Variant ↔ approval, each backed by a source. Approvals are per-variant and
 * never copied model-to-model; UL 258 (sprinkler trim) is not UL 842.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_variant_approvals')) {
            return;
        }

        Schema::connection('catalog')->create('product_variant_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id')->index();
            $table->unsignedBigInteger('approval_id')->index();
            $table->string('certificate_number')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('scope')->nullable(); // e.g. "Automatic Sprinkler Trim"
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('verification_status')->default('pending')->index();
            $table->timestamps();

            $table->unique(['product_variant_id', 'approval_id', 'scope'], 'pva_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_variant_approvals');
    }
};
