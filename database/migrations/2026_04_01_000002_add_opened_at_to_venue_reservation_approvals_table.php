<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_reservation_approvals', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable()->after('e_signature_used');
        });
    }

    public function down(): void
    {
        Schema::table('venue_reservation_approvals', function (Blueprint $table) {
            $table->dropColumn('opened_at');
        });
    }
};
