<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_address_type', 20)->nullable()->after('notes');   // 'detailed' | 'national'
            $table->string('delivery_building_no', 20)->nullable()->after('delivery_address_type');
            $table->string('delivery_street', 200)->nullable()->after('delivery_building_no');
            $table->string('delivery_district', 100)->nullable()->after('delivery_street');
            $table->string('delivery_city', 100)->nullable()->after('delivery_district');
            $table->string('delivery_region', 100)->nullable()->after('delivery_city');
            $table->string('delivery_postal_code', 20)->nullable()->after('delivery_region');
            $table->string('delivery_additional_no', 20)->nullable()->after('delivery_postal_code');
            $table->string('delivery_country', 5)->nullable()->default('SA')->after('delivery_additional_no');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_address_type',
                'delivery_building_no',
                'delivery_street',
                'delivery_district',
                'delivery_city',
                'delivery_region',
                'delivery_postal_code',
                'delivery_additional_no',
                'delivery_country',
            ]);
        });
    }
};
