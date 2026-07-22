<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a human-readable failure reason to research imports so a failed import
 * shows *why* it failed in the UI instead of a bare "Failed" status.
 */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (! Schema::connection('catalog')->hasTable('research_catalog_imports')) {
            return;
        }
        if (Schema::connection('catalog')->hasColumn('research_catalog_imports', 'error_message')) {
            return;
        }

        Schema::connection('catalog')->table('research_catalog_imports', function (Blueprint $table) {
            $table->text('error_message')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (Schema::connection('catalog')->hasColumn('research_catalog_imports', 'error_message')) {
            Schema::connection('catalog')->table('research_catalog_imports', function (Blueprint $table) {
                $table->dropColumn('error_message');
            });
        }
    }
};
