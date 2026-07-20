<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records each uploaded file's content hash on the document row.
 *
 * The parse and answer caches are keyed on this hash, and pricing needs it to
 * find a previously priced (file, answers) combination. Computing it once at
 * upload and storing it here means the pricing job does not have to re-read the
 * file off disk just to hash it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('uploaded_documents', 'file_hash')) {
            return;
        }

        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->string('file_hash', 64)->nullable()->after('file_path')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('uploaded_documents', 'file_hash')) {
            return;
        }

        Schema::table('uploaded_documents', function (Blueprint $table) {
            $table->dropColumn('file_hash');
        });
    }
};
