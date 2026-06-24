<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Arabic-language columns alongside the existing (English/primary) ones so
 * catalog divisions and categories can keep BOTH languages at the same time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('catalog')->table('catalog_products', function (Blueprint $table) {
            $table->string('division_ar')->nullable()->after('division');
            $table->text('item_description_ar')->nullable()->after('item_description');
            $table->string('product_name_ar')->nullable()->after('product_name');
            $table->string('sub_type_ar')->nullable()->after('sub_type');
        });

        Schema::connection('catalog')->table('catalog_categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['division_ar', 'item_description_ar', 'product_name_ar', 'sub_type_ar']);
        });

        Schema::connection('catalog')->table('catalog_categories', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
