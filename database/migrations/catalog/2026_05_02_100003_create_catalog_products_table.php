<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        Schema::connection('catalog')->create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('catalog_id')->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('qimta_code')->default('')->index();
            $table->string('division')->nullable()->index();
            $table->text('item_description')->nullable();
            $table->string('sub_type')->nullable()->index();
            $table->string('product_name')->default('')->index();
            $table->string('type_of_material')->nullable();
            $table->string('size')->default('');
            $table->string('unit')->default('');
            $table->string('lead_time')->nullable();
            $table->string('source_file')->nullable()->index();
            $table->unsignedBigInteger('import_batch_id')->nullable()->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->json('raw_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['catalog_id', 'category_id']);
            $table->index(['catalog_id', 'division']);
        });

        // Prefix-length unique index to stay within MySQL's 3072-byte key limit (utf8mb4 = 4 bytes/char)
        // qimta_code(100) + product_name(191) + size(50) + unit(20) = 361 chars × 4 = 1444 bytes
        DB::connection('catalog')->statement(
            'ALTER TABLE catalog_products ADD UNIQUE products_variant_unique (qimta_code(100), product_name(191), size(50), unit(20))'
        );
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('catalog_products');
    }
};
