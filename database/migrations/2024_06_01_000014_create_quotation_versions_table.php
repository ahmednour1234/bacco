<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('quotation_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number')->default(1);
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete();
            // Possible values: draft, sent, accepted, rejected, expired
            $table->string('status', 20)->default('draft')->index();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('quotation_request_id');
            $table->unique(['quotation_request_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_versions');
    }
};
