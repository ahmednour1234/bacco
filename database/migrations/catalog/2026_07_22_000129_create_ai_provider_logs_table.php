<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Low-level provider call log. NEVER stores API secrets or Authorization
 * headers — payloads are scrubbed before persisting.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('ai_provider_logs')) {
            return;
        }

        Schema::connection('catalog')->create('ai_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('model')->nullable();
            $table->string('endpoint')->nullable();
            $table->string('request_id')->nullable()->index();
            $table->json('request_payload')->nullable();  // scrubbed
            $table->unsignedSmallInteger('response_status')->nullable()->index();
            $table->json('response_payload')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('ai_provider_logs');
    }
};
