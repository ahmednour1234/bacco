<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dedicated store for AI results.
 *
 * Extractions, prices, verdicts, market ranges and validation questions all
 * lived in the default `cache` table, which `cache:clear` empties — and
 * `optimize:clear`, run on every deploy, calls it. So each deploy silently
 * discarded work that had already been paid for, and the next upload of an
 * unchanged BOQ produced different numbers.
 *
 * Same schema as Laravel's cache table so the database driver can use it
 * unchanged; only the name differs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_cache')) {
            return;
        }

        Schema::create('ai_cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_cache');
    }
};
