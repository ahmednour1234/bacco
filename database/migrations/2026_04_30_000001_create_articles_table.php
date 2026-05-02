<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('title_en');
            $table->string('title_ar');
            $table->longText('desc_en')->nullable();
            $table->longText('desc_ar')->nullable();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
