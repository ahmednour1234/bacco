<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('uuid');
        });

        // Back-fill slugs for existing rows from title_en
        DB::table('articles')->orderBy('id')->each(function ($row) {
            $base = Str::slug($row->title_en);
            $slug = $base;
            $i    = 1;
            while (DB::table('articles')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            DB::table('articles')->where('id', $row->id)->update(['slug' => $slug]);
        });

        // Make non-nullable now that all rows are filled
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
