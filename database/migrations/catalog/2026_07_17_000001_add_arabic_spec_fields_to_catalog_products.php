<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the remaining Arabic spec columns (material / size / lead time) so the
 * whole technical configuration on a catalog item can render in Arabic. The
 * name/division/description Arabic columns already exist from an earlier
 * migration; this only fills the gap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('catalog')->table('catalog_products', function (Blueprint $table) {
            if (! Schema::connection('catalog')->hasColumn('catalog_products', 'type_of_material_ar')) {
                $table->string('type_of_material_ar')->nullable()->after('type_of_material');
            }
            if (! Schema::connection('catalog')->hasColumn('catalog_products', 'size_ar')) {
                $table->string('size_ar')->nullable()->after('size');
            }
            if (! Schema::connection('catalog')->hasColumn('catalog_products', 'lead_time_ar')) {
                $table->string('lead_time_ar')->nullable()->after('lead_time');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['type_of_material_ar', 'size_ar', 'lead_time_ar']);
        });
    }
};
