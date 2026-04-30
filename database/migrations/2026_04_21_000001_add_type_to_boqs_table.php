<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            // Add type column after status (values: 'tender' or 'awarded')
            $table->string('type', 20)->default('tender')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
