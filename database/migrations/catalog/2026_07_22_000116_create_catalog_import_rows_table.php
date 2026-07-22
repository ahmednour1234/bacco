<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original Excel row exactly as imported (raw values preserved), plus a
 * row_hash so the same row cannot be imported twice within one file.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('catalog_import_rows')) {
            return;
        }

        Schema::connection('catalog')->create('catalog_import_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_import_id')->index();
            $table->string('sheet_name')->nullable()->index();
            $table->unsignedInteger('excel_row_number')->nullable();

            // Raw values straight from the sheet (never normalized in place).
            $table->string('source_code')->nullable()->index();
            $table->text('division_raw')->nullable();
            $table->text('category_raw')->nullable();
            $table->text('item_description_raw')->nullable();
            $table->text('material_raw')->nullable();
            $table->text('manufacturer_raw')->nullable();
            $table->text('connection_raw')->nullable();
            $table->text('size_raw')->nullable();
            $table->text('pressure_raw')->nullable();
            $table->text('standard_raw')->nullable();
            $table->text('approval_raw')->nullable();
            $table->text('unit_raw')->nullable();

            $table->json('original_row')->nullable();   // full untouched row
            $table->json('normalized_row')->nullable(); // parsed structure
            $table->string('row_hash', 64)->index();
            $table->string('import_status')->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('product_family_id')->nullable()->index();
            $table->timestamps();

            // A given row (by hash) may only be imported once per file.
            $table->unique(['catalog_import_id', 'row_hash']);
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_import_rows');
    }
};
