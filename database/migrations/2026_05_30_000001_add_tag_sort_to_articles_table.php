<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('tag')->nullable()->after('image');           // market | tech | case
            $table->unsignedSmallInteger('sort_order')->default(0)->after('tag');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['tag', 'sort_order']);
        });
    }
};
