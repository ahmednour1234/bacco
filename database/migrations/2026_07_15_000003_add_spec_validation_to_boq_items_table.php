<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boq_items', function (Blueprint $table) {
            // Mirror of the quotation_items spec-validation columns so the verdict
            // is also visible when an admin browses the BOQ directly.
            $table->string('validation_status', 30)->nullable()->after('confidence');
            $table->string('suggested_unit', 50)->nullable()->after('validation_status');
            $table->json('missing_specs')->nullable()->after('suggested_unit');
            $table->json('spec_answers')->nullable()->after('missing_specs');
            $table->string('validation_note', 255)->nullable()->after('spec_answers');
            $table->timestamp('validated_at')->nullable()->after('validation_note');
        });
    }

    public function down(): void
    {
        Schema::table('boq_items', function (Blueprint $table) {
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
