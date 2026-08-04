<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE venue_reservations MODIFY COLUMN venue_name VARCHAR(255) NOT NULL DEFAULT 'Other'");
        DB::statement("ALTER TABLE venue_reservations MODIFY COLUMN event_id BIGINT UNSIGNED NULL");
        
        // Also add event_title column if it doesn't exist
        if (!Schema::hasColumn('venue_reservations', 'event_title')) {
            Schema::table('venue_reservations', function (Blueprint $table) {
                $table->string('event_title')->nullable()->after('event_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_reservations', function (Blueprint $table) {
            $table->dropColumn('event_title');
        });
    }
};
