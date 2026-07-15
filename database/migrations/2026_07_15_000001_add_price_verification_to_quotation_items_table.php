<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // The AI-verified (sanity-checked) unit price against Saudi market/supplier
            // sources. May equal unit_price (confirmed) or differ (corrected).
            $table->decimal('verified_price', 12, 2)->nullable()->after('price_source');

            // Verification verdict for the price:
            //   null       = not verified yet
            //   'confirmed'= AI confirmed the price is realistic for the Saudi market
            //   'corrected'= AI found the price unrealistic and supplied a corrected one
            //   'flagged'  = AI could not confirm; price is doubtful, needs human review
            $table->string('price_verdict', 20)->nullable()->after('verified_price');

            // Short human-readable reason from the AI (e.g. "متوسط سعر السوق أعلى بكثير").
            $table->string('price_verification_note', 255)->nullable()->after('price_verdict');

            // When the verification pass ran.
            $table->timestamp('price_verified_at')->nullable()->after('price_verification_note');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'verified_price',
                'price_verdict',
                'price_verification_note',
                'price_verified_at',
            ]);
        });
    }
};
