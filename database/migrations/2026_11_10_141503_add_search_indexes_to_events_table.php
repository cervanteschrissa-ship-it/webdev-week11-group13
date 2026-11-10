<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Full-text index (the schema builder can create this one)
        Schema::table('events', function (Blueprint $table) {
            $table->fullText('description');
        });

        // The schema builder cannot create these two, so we use raw SQL
        DB::statement("CREATE INDEX events_dress_code_btree ON events ((details->>'dress_code'))");
        DB::statement('CREATE INDEX events_details_gin ON events USING GIN (details)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS events_details_gin');
        DB::statement('DROP INDEX IF EXISTS events_dress_code_btree');

        Schema::table('events', function (Blueprint $table) {
            $table->dropFullText(['description']);
        });
    }
};