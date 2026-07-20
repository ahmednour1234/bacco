<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the reuse keys on the quotation itself.
 *
 * Pricing is dispatched from several places — the create page, the show page,
 * two BOQ pages, admin — and only the create page knew the file hash and the
 * answer hash. The other paths priced with no context, so the priced-answer
 * cache never hit for them and the same BOQ re-priced against the AI.
 *
 * Keeping both on the quotation means every dispatch site, and the job itself,
 * reads them from one place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('quotation_requests', 'boq_file_hash')) {
                $table->string('boq_file_hash', 64)->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('quotation_requests', 'answers_hash')) {
                $table->string('answers_hash', 64)->nullable()->after('boq_file_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            foreach (['boq_file_hash', 'answers_hash'] as $column) {
                if (Schema::hasColumn('quotation_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
