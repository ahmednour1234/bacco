<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->string('guest_name', 255)->nullable()->after('client_id');
            $table->string('guest_email', 255)->nullable()->after('guest_name');
            $table->string('guest_phone', 30)->nullable()->after('guest_email');

            // Guest leads are looked up by email when reconciling them with a
            // real account, so keep that path indexed.
            $table->index('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropIndex(['guest_email']);
            $table->dropColumn(['guest_name', 'guest_email', 'guest_phone']);
        });
    }
};
