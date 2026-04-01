<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('qr_token', 64)->unique();
            $table->timestamp('qr_expires_at')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'event_id']); // one registration per student per event
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
