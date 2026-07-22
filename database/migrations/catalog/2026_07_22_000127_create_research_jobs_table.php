<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One research request (a staged step) sent to the AI provider. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('research_jobs')) {
            return;
        }

        Schema::connection('catalog')->create('research_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('product_family_id')->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
            $table->string('job_type')->index();
            $table->string('provider')->default('deepseek')->index();
            $table->string('model_name')->nullable();
            $table->text('research_query')->nullable();
            $table->json('input_payload')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('priority')->default(5)->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['product_family_id', 'status'], 'rj_family_status_idx');
            $table->index(['status', 'priority'], 'rj_status_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('research_jobs');
    }
};
