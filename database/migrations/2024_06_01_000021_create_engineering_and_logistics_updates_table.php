<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineering_updates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            // Possible values: pending, in_progress, reviewing, approved, rejected, completed
            $table->string('status', 30)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });

        Schema::create('logistics_updates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            // Possible values: pending, preparing, dispatched, in_transit, delivered, failed
            $table->string('status', 30)->index();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_updates');
        Schema::dropIfExists('engineering_updates');
    }
};
