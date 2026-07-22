<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deep Catalog Expansion researches a MANUFACTURER's published catalog, not a
 * single imported Excel row, so those jobs have no product family to point at.
 *
 * The column was NOT NULL from when every job began life as a family research
 * job; expansion is the first legitimate case of a job without one.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (! Schema::connection('catalog')->hasTable('research_jobs')) {
            return;
        }

        // Raw DDL: doctrine/dbal is not installed, so ->change() is unavailable.
        DB::connection('catalog')->statement(
            'ALTER TABLE `research_jobs` MODIFY `product_family_id` BIGINT UNSIGNED NULL'
        );
    }

    public function down(): void
    {
        if (! Schema::connection('catalog')->hasTable('research_jobs')) {
            return;
        }

        // Rows created by expansion have no family, so they must go before the
        // column can be NOT NULL again.
        DB::connection('catalog')->table('research_jobs')->whereNull('product_family_id')->delete();

        DB::connection('catalog')->statement(
            'ALTER TABLE `research_jobs` MODIFY `product_family_id` BIGINT UNSIGNED NOT NULL'
        );
    }
};
