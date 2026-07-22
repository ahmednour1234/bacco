<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approvals / certifications (UL 258, UL 842, FM, LPCB, WRAS, NSF/ANSI 61,
 * SASO, Civil Defense…). Code is distinct from name so UL 258 (sprinkler trim)
 * is never conflated with UL 842 (flammable fluids).
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('approvals')) {
            return;
        }

        Schema::connection('catalog')->create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('issuing_body')->nullable()->index();
            $table->string('approval_code')->nullable()->index(); // UL 258 vs UL 842
            $table->string('normalized_key')->unique();           // issuing_body + code
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('approvals');
    }
};
