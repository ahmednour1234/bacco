<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engineering_updates', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex(['project_id']);
            $table->dropColumn('project_id');

            $table->foreignId('order_id')->after('uuid')->constrained()->cascadeOnDelete();
            $table->index('order_id');
        });

        Schema::table('logistics_updates', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex(['project_id']);
            $table->dropColumn('project_id');

            $table->foreignId('order_id')->after('uuid')->constrained()->cascadeOnDelete();
            $table->index('order_id');
        });
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
