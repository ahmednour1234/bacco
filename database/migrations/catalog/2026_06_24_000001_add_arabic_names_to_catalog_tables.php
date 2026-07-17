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
        $conn = Schema::connection('catalog');

        $conn->table('catalog_products', function (Blueprint $table) use ($conn) {
            if (! $conn->hasColumn('catalog_products', 'division_ar')) {
                $table->string('division_ar')->nullable()->after('division');
            }
            if (! $conn->hasColumn('catalog_products', 'item_description_ar')) {
                $table->text('item_description_ar')->nullable()->after('item_description');
            }
            if (! $conn->hasColumn('catalog_products', 'product_name_ar')) {
                $table->string('product_name_ar')->nullable()->after('product_name');
            }
            if (! $conn->hasColumn('catalog_products', 'sub_type_ar')) {
                $table->string('sub_type_ar')->nullable()->after('sub_type');
            }
        });

        if (! $conn->hasColumn('catalog_categories', 'name_ar')) {
            $conn->table('catalog_categories', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name');
            });
        }
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
