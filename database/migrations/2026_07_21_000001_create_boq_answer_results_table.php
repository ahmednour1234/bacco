<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers what a specific set of answers produced for a specific file.
 *
 * The parse of a file is already reused (boq_parse_results), but the questions
 * it raises are answered by the user, and those answers change the rows — and
 * therefore the prices. Two people who upload the same BOQ and answer the same
 * way should get the same quotation without paying for the AI again; someone who
 * answers differently must get a fresh result.
 *
 * Keyed on the file's content hash plus a hash of the answers, so:
 *   - same file + same answers → reuse
 *   - same file + different answers → miss, ask the AI
 *   - different file → miss, regardless of answers
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boq_answer_results')) {
            return;
        }

        Schema::create('boq_answer_results', function (Blueprint $table) {
            $table->id();

            // SHA-256 of the uploaded file's bytes.
            $table->string('file_hash', 64);

            // SHA-256 of the normalised answer set. Empty-answers case has its
            // own stable hash, so "answered nothing" is a cacheable outcome too.
            $table->string('answers_hash', 64);

            // What the user was asked and what they chose — kept so the result is
            // explainable later, not just reproducible.
            $table->longText('questions')->nullable();
            $table->longText('answers')->nullable();

            // The priced rows this file+answers combination produced.
            $table->longText('priced_items');

            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // One row per (file, answer-set); the pair is the lookup key.
            $table->unique(['file_hash', 'answers_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_answer_results');
    }
};
