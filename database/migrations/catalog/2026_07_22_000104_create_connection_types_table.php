<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Connection types (Female Threaded, NPT, Press-Fit, Grooved, Flanged…). */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('connection_types')) {
            return;
        }

        Schema::connection('catalog')->create('connection_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->unique();
            $table->json('aliases')->nullable(); // NPT / N.P.T. / FNPT …
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('connection_types');
    }
};
