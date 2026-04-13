<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make projects.order_id nullable (project is now the top-level entity)
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreignId('order_id')->nullable()->change();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });

        // 2. Create boqs table
        Schema::create('boqs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('boq_no')->unique()->index();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            // Possible values: draft, submitted, completed, cancelled
            $table->string('status', 20)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('client_id');
        });

        // 3. Create boq_items table
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('boq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 15, 3);
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 255)->nullable();
            $table->string('brand', 255)->nullable();
            // Possible values: pending, sourcing, sourced, rejected
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('engineering_required')->default(false);
            $table->decimal('confidence', 5, 2)->nullable();
            $table->json('raw_data')->nullable();
            $table->boolean('ai_extracted')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('boq_id');
        });

        // 4. Add project_id to quotation_requests
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('quotation_no')->constrained()->nullOnDelete();
            $table->foreignId('boq_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });

        // 5. Add project_id to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('order_no')->constrained()->nullOnDelete();
        });

        // 6. Add boq_id to uploaded_documents
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->foreignId('boq_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->dropForeign(['boq_id']);
            $table->dropColumn('boq_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['boq_id']);
            $table->dropColumn(['project_id', 'boq_id']);
        });

        Schema::dropIfExists('boq_items');
        Schema::dropIfExists('boqs');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreignId('order_id')->constrained()->cascadeOnDelete()->change();
        });
    }
};
