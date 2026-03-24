<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('division')->nullable()->after('name');
            $table->string('model_type')->nullable()->after('division');
            $table->decimal('unit_price', 12, 2)->nullable()->after('model_type');
            $table->decimal('engineering_price', 12, 2)->nullable()->after('unit_price');
            $table->decimal('installation_price', 12, 2)->nullable()->after('engineering_price');
            $table->decimal('margin_percentage', 5, 2)->default(15)->after('installation_price');
            $table->string('datasheet_path')->nullable()->after('margin_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'division', 'model_type', 'unit_price', 'engineering_price',
                'installation_price', 'margin_percentage', 'datasheet_path',
            ]);
        });
    }
};
