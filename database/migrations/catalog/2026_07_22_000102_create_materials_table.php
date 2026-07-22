<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Materials (Brass, DZR Brass, Bronze ASTM B584 C84400, PTFE…). */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('materials')) {
            return;
        }

        Schema::connection('catalog')->create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->unique();
            $table->string('material_category')->nullable()->index(); // metal | polymer | elastomer …
            $table->string('standard_designation')->nullable(); // e.g. ASTM B584 C84400
            $table->json('aliases')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('materials');
    }
};
