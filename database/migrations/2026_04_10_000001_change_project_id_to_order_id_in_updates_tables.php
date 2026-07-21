<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Swaps project_id for order_id on both updates tables.
     *
     * order_id is added nullable rather than as a plain foreignId(). A NOT NULL
     * column with no default is legal on an empty MySQL table but SQLite refuses
     * it outright, which made every test in the suite fail during migration —
     * before a single assertion ran. Nullable works on both, and these rows are
     * created with an order_id anyway.
     */
    public function up(): void
    {
        foreach (['engineering_updates', 'logistics_updates'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'project_id')) {
                    // SQLite has no real DROP FOREIGN KEY, and the index goes
                    // with the column, so only MySQL needs the explicit drops.
                    if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                        $table->dropForeign(['project_id']);
                        $table->dropIndex(['project_id']);
                    }

                    $table->dropColumn('project_id');
                }
            });

            if (Schema::hasColumn($tableName, 'order_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('uuid')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->index('order_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('engineering_updates', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');

            $table->foreignId('project_id')->after('uuid')->constrained()->cascadeOnDelete();
            $table->index('project_id');
        });

        Schema::table('logistics_updates', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');

            $table->foreignId('project_id')->after('uuid')->constrained()->cascadeOnDelete();
            $table->index('project_id');
        });
    }
};
