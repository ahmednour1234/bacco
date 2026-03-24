<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * We use a custom table name (qimta_notifications) to avoid conflict
     * with Laravel's built-in notifications table.
     */
    public function up(): void
    {
        Schema::create('qimta_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('body');
            $table->string('type', 100)->nullable()->index();
            $table->jsonb('data')->nullable();
            // Possible values: database, email, sms
            $table->string('channel', 20)->default('database')->index();
            $table->timestamps();
        });

        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('notification_id')->constrained('qimta_notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Possible values: database, email, sms
            $table->string('channel', 20)->index();
            $table->string('type', 100);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('qimta_notifications');
    }
};
