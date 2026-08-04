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
        Schema::table('registrations', function (Blueprint $table) {
            $table->index(['user_id', 'event_id'], 'idx_registrations_user_event');
            $table->index('qr_token', 'idx_registrations_qr_token');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('registration_id', 'idx_attendances_registration');
            $table->index('status', 'idx_attendances_status');
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'idx_notifs_user_read');
        });

        Schema::table('system_audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_sys_audit_user_created');
        });

        Schema::table('venue_reservations', function (Blueprint $table) {
            $table->index(['reserved_date', 'status'], 'idx_venue_res_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('idx_registrations_user_event');
            $table->dropIndex('idx_registrations_qr_token');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_registration');
            $table->dropIndex('idx_attendances_status');
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifs_user_read');
        });

        Schema::table('system_audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_sys_audit_user_created');
        });

        Schema::table('venue_reservations', function (Blueprint $table) {
            $table->dropIndex('idx_venue_res_date_status');
        });
    }
};
