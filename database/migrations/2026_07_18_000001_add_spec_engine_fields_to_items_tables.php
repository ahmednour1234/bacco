<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fields for the Product Specification & Pricing Qualification Engine.
 *
 * The earlier spec-validation pass only recorded a unit verdict plus a list of
 * questions. The engine additionally classifies each line (supply vs service),
 * normalises its unit, separates confirmed specs from inferred ones, and records
 * quantity / unit / compatibility warnings found by the project-level pass.
 *
 * Guarded with hasColumn so it is safe to re-run against a partially-migrated DB.
 */
return new class extends Migration
{
    /** table => [column => closure-safe definition name] */
    private const TABLES = ['quotation_items', 'boq_items'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                // SUPPLY_PRODUCT | CUSTOM_MANUFACTURED_PRODUCT | SOFTWARE_OR_LICENSE
                // | SERVICE | INSTALLATION | CONSULTATION | UNSUPPORTED_ITEM | UNCLEAR_ITEM
                if (! Schema::hasColumn($table, 'classification')) {
                    $t->string('classification', 40)->nullable()->index();
                }

                // READY_TO_PRICE | READY_WITH_ASSUMPTIONS | BLOCKED_MISSING_SPECIFICATIONS
                // | INVALID_QUANTITY_OR_UNIT | COMPATIBILITY_REVIEW_REQUIRED | NOT_A_SUPPLY_PRODUCT
                if (! Schema::hasColumn($table, 'pricing_status')) {
                    $t->string('pricing_status', 40)->nullable()->index();
                }

                // False for services/installation so they never enter a supply quotation.
                if (! Schema::hasColumn($table, 'supplyable')) {
                    $t->boolean('supplyable')->default(true);
                }

                if (! Schema::hasColumn($table, 'normalized_unit')) {
                    $t->string('normalized_unit', 20)->nullable();
                }

                if (! Schema::hasColumn($table, 'normalized_product_name')) {
                    $t->string('normalized_product_name', 500)->nullable();
                }

                // Specs the client actually supplied. {"ram":"16 GB"}
                if (! Schema::hasColumn($table, 'confirmed_specifications')) {
                    $t->json('confirmed_specifications')->nullable();
                }

                // Safe industry-standard defaults the engine applied, always labelled
                // as assumptions so a budgetary price is never mistaken for a firm one.
                if (! Schema::hasColumn($table, 'inferred_specifications')) {
                    $t->json('inferred_specifications')->nullable();
                }

                // Human-readable assumption statements shown next to the price.
                if (! Schema::hasColumn($table, 'assumptions')) {
                    $t->json('assumptions')->nullable();
                }

                // Findings from the project-level pass. Each is {code, severity, message}.
                if (! Schema::hasColumn($table, 'quantity_warnings')) {
                    $t->json('quantity_warnings')->nullable();
                }
                if (! Schema::hasColumn($table, 'unit_warnings')) {
                    $t->json('unit_warnings')->nullable();
                }
                if (! Schema::hasColumn($table, 'compatibility_warnings')) {
                    $t->json('compatibility_warnings')->nullable();
                }

                // Clean description for the final quotation — specs only, never questions.
                if (! Schema::hasColumn($table, 'recommended_final_description')) {
                    $t->text('recommended_final_description')->nullable();
                }

                if (! Schema::hasColumn($table, 'pricing_basis')) {
                    $t->string('pricing_basis', 255)->nullable();
                }

                if (! Schema::hasColumn($table, 'confidence_score')) {
                    $t->unsignedTinyInteger('confidence_score')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'classification', 'pricing_status', 'supplyable', 'normalized_unit',
            'normalized_product_name', 'confirmed_specifications', 'inferred_specifications',
            'assumptions', 'quantity_warnings', 'unit_warnings', 'compatibility_warnings',
            'recommended_final_description', 'pricing_basis', 'confidence_score',
        ];

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $present = array_values(array_filter(
                $columns,
                fn ($c) => Schema::hasColumn($table, $c)
            ));

            if ($present !== []) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($present));
            }
        }
    }
};
