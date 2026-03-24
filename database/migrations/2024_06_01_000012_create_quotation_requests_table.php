<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('quotation_no')->unique()->index();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('users')->nullOnDelete();
            // Possible values: manual, website, api
            $table->string('source_type', 20)->default('manual')->index();
            // Possible values: draft, submitted, in_review, quoted, accepted, rejected, cancelled
            $table->string('status', 30)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_requests');
    }
};
