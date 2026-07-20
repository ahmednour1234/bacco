<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permanent record of what a given BOQ file produced.
 *
 * The AI results already live in a cache, but a cache is allowed to disappear —
 * it expires, it gets flushed, a driver gets swapped. When that happens the same
 * file is parsed again and, because the model is not deterministic, produces
 * different rows, different questions and different prices. Re-uploading one BOQ
 * should never change the quotation.
 *
 * Keyed on the file's content hash, not its name: a renamed copy of the same
 * document reuses the parse, and an edited file under the same name does not.
 * That is what makes it safe — an edit always re-parses.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boq_parse_results')) {
            return;
        }

        Schema::create('boq_parse_results', function (Blueprint $table) {
            $table->id();

            // SHA-256 of the file's bytes. Unique: one result per document.
            $table->string('file_hash', 64)->unique();

            // Kept for humans reading the table, never for matching.
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // The extracted rows, exactly as they were first parsed.
            $table->longText('items');

            // The validation questions that set of rows produced.
            $table->longText('questions')->nullable();

            // How many times this document has been uploaded. Useful for
            // knowing whether the reuse is actually earning its keep.
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_parse_results');
    }
};
