<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The raw + parsed result of a research job, with validation counts. */
return new class extends Migration
{
    protected $connection = 'catalog';

    public function up(): void
    {
        if (Schema::connection('catalog')->hasTable('research_job_results')) {
            return;
        }

        Schema::connection('catalog')->create('research_job_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('research_job_id')->index();
            $table->longText('raw_response')->nullable();
            $table->json('parsed_response')->nullable();
            $table->string('validation_status')->default('pending')->index();
            $table->json('validation_errors')->nullable();
            $table->unsignedInteger('discovered_count')->default(0);
            $table->unsignedInteger('accepted_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('catalog')->dropIfExists('research_job_results');
    }
};
