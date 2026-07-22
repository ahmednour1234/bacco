<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pressure ratings (300 PSI, 600 WOG, PN16, Class 150…). */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('pressure_ratings')) {
            return;
        }

        Schema::connection('catalog')->create('pressure_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('rating_name');
            $table->decimal('numeric_value', 12, 3)->nullable();
            $table->string('unit')->nullable();           // psi | bar | pn | class
            $table->string('pressure_class')->nullable(); // WOG | WSP | Class …
            $table->string('service_type')->nullable();   // water | oil | gas …
            $table->string('normalized_value')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('pressure_ratings');
    }
};
