<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('venue_reservations', function (Blueprint $table) {
            $table->foreignId('override_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('override_at')->nullable()->after('override_by');
            $table->text('override_reason')->nullable()->after('override_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_reservations', function (Blueprint $table) {
            $table->dropForeign(['override_by']);
            $table->dropColumn(['override_by', 'override_at', 'override_reason']);
        });
    }
};
