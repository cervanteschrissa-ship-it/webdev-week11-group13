<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // starts_at already exists from Week 10, so it is not added again.
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->jsonb('details')->nullable(); // dress_code, capacity, tags
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['description', 'details']);
        });
    }
};
