<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('unit_id');
            $table->string('brand', 100)->nullable()->after('category');
            $table->boolean('engineering_required')->default(false)->after('brand');
            $table->decimal('confidence', 5, 2)->nullable()->after('engineering_required');
            $table->jsonb('raw_data')->nullable()->after('confidence');
            $table->boolean('ai_extracted')->default(false)->after('raw_data');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'brand', 'engineering_required', 'confidence', 'raw_data', 'ai_extracted']);
        });
    }
};
