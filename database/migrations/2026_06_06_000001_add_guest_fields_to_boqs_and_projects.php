<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── boqs: make client_id nullable, add guest_token ────────────────────
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
            $table->string('guest_token', 36)->nullable()->unique()->after('client_id');
        });

        // ── projects: make client_id nullable, add is_guest ───────────────────
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_guest')->default(false)->after('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('guest_token');
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('is_guest');
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
