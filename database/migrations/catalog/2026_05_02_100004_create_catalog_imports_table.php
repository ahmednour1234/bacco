<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        Schema::connection('catalog')->create('catalog_imports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('catalog_id')->nullable()->index();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending')->index();
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('inserted_rows')->default(0);
            $table->unsignedBigInteger('updated_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_imports');
    }
};
