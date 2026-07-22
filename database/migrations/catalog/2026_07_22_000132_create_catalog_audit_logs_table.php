<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for the research module (upload, research started/paused, AI
 * request/result, product created/verified/rejected, source changed, export…).
 * Stores before/after for meaningful technical edits.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('catalog_audit_logs')) {
            return;
        }

        Schema::connection('catalog')->create('catalog_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event')->index();
            $table->string('auditable_type')->nullable()->index();
            $table->unsignedBigInteger('auditable_id')->nullable()->index();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('created_at')->nullable()->index();

            $table->index(['auditable_type', 'auditable_id'], 'cal_auditable_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_audit_logs');
    }
};
