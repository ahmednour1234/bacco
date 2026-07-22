<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Excel upload record for the research module. Named distinctly from the
 * existing pricing `catalog_imports`. The original file is always stored and
 * never deleted.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('research_catalog_imports')) {
            return;
        }

        Schema::connection('catalog')->create('research_catalog_imports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('original_file_name');
            $table->string('stored_file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('sheets_count')->default(0);
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('imported_rows')->default(0);
            $table->unsignedBigInteger('duplicate_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);
            $table->string('status')->default('uploaded')->index();
            $table->json('column_mapping')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('research_catalog_imports');
    }
};
