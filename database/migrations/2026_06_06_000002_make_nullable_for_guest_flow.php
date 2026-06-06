<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── uploaded_documents: make uploaded_by nullable ─────────────────────
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->unsignedBigInteger('uploaded_by')->nullable()->change();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── quotation_requests: make client_id nullable ───────────────────────
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->unsignedBigInteger('uploaded_by')->nullable(false)->change();
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
