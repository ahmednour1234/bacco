<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereIn('status', ['completed', 'cancelled', 'refunded'])
            ->update(['status' => 'closed']);

        DB::table('orders')
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered'])
            ->update(['status' => 'open']);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('open')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });
    }
};
