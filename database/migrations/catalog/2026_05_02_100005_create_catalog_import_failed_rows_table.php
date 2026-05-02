<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        Schema::connection('catalog')->create('catalog_import_failed_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_import_id')->index();
            $table->unsignedBigInteger('row_number');
            $table->json('row_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_import_failed_rows');
    }
};
