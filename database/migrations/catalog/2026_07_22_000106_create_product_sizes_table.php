<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical sizes. Raw display kept (1 1/4", 1¼", 1.25 inch, DN32) but a
 * normalized_value unifies them for dedup/matching.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('product_sizes')) {
            return;
        }

        Schema::connection('catalog')->create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('display_value');           // as shown by manufacturer
            $table->string('normalized_value')->unique(); // canonical key
            $table->string('nominal_size')->nullable();
            $table->string('unit')->nullable();        // inch | mm | dn
            $table->string('dn_value')->nullable()->index();
            $table->decimal('inch_decimal', 10, 4)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('product_sizes');
    }
};
