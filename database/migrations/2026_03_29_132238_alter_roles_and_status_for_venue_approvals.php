<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add student_department to users role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','organizer','student','adviser','department_head','dean','executive_director','student_development','program_chair','student_department') NOT NULL DEFAULT 'student'");
        
        // Update venue_reservations status to support long approval chain
        DB::statement("ALTER TABLE venue_reservations MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending_student_dev'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE venue_reservations MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
