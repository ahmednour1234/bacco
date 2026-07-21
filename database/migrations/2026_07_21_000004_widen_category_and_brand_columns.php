<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widens category/brand so an over-long AI value cannot fail the insert.
 *
 * The model routinely returns a full description in `category` — for example
 * "Cocktail Unit Ice well adjacent to chopping area with waste chute and pull
 * out bin, next to sink with blender station". At varchar(100) that INSERT
 * failed with SQLSTATE[22001], and because it failed the whole chunk produced
 * no rows: every reuse table stayed empty and each upload re-priced from
 * scratch.
 *
 * The values are also clamped in code now. This is the second line of defence,
 * so a long value truncates rather than destroying the row.
 *
 * Raw SQL because MODIFY does not need doctrine/dbal, which is not installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite ignores varchar lengths entirely — a 200-char value fits a
        // "varchar(100)" column — so there is nothing to widen, and it has no
        // MODIFY clause to do it with. Only MySQL enforces the limit that broke
        // the insert, so only MySQL needs the change.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach (['quotation_items', 'boq_items'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (['category', 'brand'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) NULL");
            }
        }
    }

    public function down(): void
    {
        // Deliberately not narrowed again: shrinking the column would truncate
        // data that is already stored.
    }
};
