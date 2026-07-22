<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Polymorphic review queue for anything needing human eyes. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_review_queue')) {
            return;
        }

        Schema::connection('catalog')->create('product_review_queue', function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type')->index();
            $table->unsignedBigInteger('reviewable_id')->index();
            $table->string('reason')->index();
            $table->string('severity')->default('medium')->index();
            $table->json('current_data')->nullable();
            $table->json('suggested_data')->nullable();
            $table->string('status')->default('open')->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id'], 'prq_reviewable_idx');
            $table->index(['status', 'severity'], 'prq_status_severity_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_review_queue');
    }
};
