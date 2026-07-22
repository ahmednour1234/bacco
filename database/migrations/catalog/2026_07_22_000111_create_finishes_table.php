<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Surface finishes / colors (Chrome-Plated, Nickel-Plated, Natural…). */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('finishes')) {
            return;
        }

        Schema::connection('catalog')->create('finishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->unique();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('finishes');
    }
};
