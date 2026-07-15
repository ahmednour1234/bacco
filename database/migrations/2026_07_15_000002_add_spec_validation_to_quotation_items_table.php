<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // Pre-pricing spec validation verdict:
            //   null | 'valid' | 'unit_error' | 'needs_information'
            $table->string('validation_status', 30)->nullable()->after('confidence');

            // Correct unit suggested by the AI when the given unit is wrong (unit_error).
            $table->string('suggested_unit', 50)->nullable()->after('validation_status');

            // Questions the user must answer before an accurate price can be given.
            // Shape: [{"key":"size","question":"...","example":"110mm"}]
            $table->json('missing_specs')->nullable()->after('suggested_unit');

            // The user's answers, keyed by the question key. Shape: {"size":"110mm"}
            $table->json('spec_answers')->nullable()->after('missing_specs');

            // Short AI note explaining the verdict (Arabic).
            $table->string('validation_note', 255)->nullable()->after('spec_answers');

            $table->timestamp('validated_at')->nullable()->after('validation_note');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'validation_status',
                'suggested_unit',
                'missing_specs',
                'spec_answers',
                'validation_note',
                'validated_at',
            ]);
        });
    }
};
