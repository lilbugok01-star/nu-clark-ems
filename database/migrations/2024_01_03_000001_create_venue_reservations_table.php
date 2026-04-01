<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('reserved_by')->constrained('users')->onDelete('cascade');
            $table->enum('venue_name', [
                'NU Clark Gymnasium',
                'NU Clark Auditorium',
                'AVR 1',
                'AVR 2',
                'Conference Room A',
                'Conference Room B',
                'Function Hall',
                'Open Court',
                'Other',
            ])->default('NU Clark Auditorium');
            $table->date('reserved_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('expected_attendees')->default(50);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add venue_type column to events table
        Schema::table('events', function (Blueprint $table) {
            $table->string('venue_type')->nullable()->after('venue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_reservations');
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('venue_type');
        });
    }
};
