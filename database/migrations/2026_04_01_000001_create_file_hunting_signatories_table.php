<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_hunting_signatories', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('step_order');          // 1, 2, 3, 4 …
            $table->string('role');                             // maps to users.role
            $table->string('position_label');                   // display name e.g. "Student Development"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default chain matching the existing workflow
        DB::table('file_hunting_signatories')->insert([
            ['step_order' => 1, 'role' => 'student_development', 'position_label' => 'Student Development Officer',  'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['step_order' => 2, 'role' => 'program_chair',       'position_label' => 'Program Chair',                'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['step_order' => 3, 'role' => 'dean',                'position_label' => 'College Dean',                 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['step_order' => 4, 'role' => 'executive_director',  'position_label' => 'Executive Director',           'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('file_hunting_signatories');
    }
};
