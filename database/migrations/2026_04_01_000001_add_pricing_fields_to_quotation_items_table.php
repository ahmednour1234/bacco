<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->nullable()->after('brand');
            // 'products' = matched from products table, 'gemini' = AI-estimated, null = not priced yet
            $table->string('price_source', 20)->nullable()->after('unit_price');
            // 'pending' | 'approved' | 'rejected' — tracks client's price-review decision
            $table->string('price_status', 30)->default('pending')->after('price_source');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'price_source', 'price_status']);
        });
    }
};
