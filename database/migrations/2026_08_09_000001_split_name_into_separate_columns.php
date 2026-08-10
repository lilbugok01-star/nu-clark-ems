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
        // Step 1: Add new columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->default('');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('surname')->after('middle_name')->default('');
        });

        // Step 2: Migrate existing name data into the new columns
        $users = DB::table('users')->select('id', 'name')->get();

        foreach ($users as $user) {
            $parts = preg_split('/\s+/', trim($user->name));

            if (count($parts) === 1) {
                // Single name only
                $firstName  = $parts[0];
                $middleName = null;
                $surname    = '';
            } elseif (count($parts) === 2) {
                // First and Last
                $firstName  = $parts[0];
                $middleName = null;
                $surname    = $parts[1];
            } else {
                // First, Middle(s), Last
                $firstName  = $parts[0];
                $surname    = array_pop($parts);
                array_shift($parts);
                $middleName = implode(' ', $parts);
            }

            DB::table('users')->where('id', $user->id)->update([
                'first_name'  => $firstName,
                'middle_name' => $middleName,
                'surname'     => $surname,
            ]);
        }

        // Step 3: Drop old name column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add name column
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id')->default('');
        });

        // Reconstruct name from parts
        $users = DB::table('users')->select('id', 'first_name', 'middle_name', 'surname')->get();

        foreach ($users as $user) {
            $name = trim(implode(' ', array_filter([
                $user->first_name,
                $user->middle_name,
                $user->surname,
            ])));

            DB::table('users')->where('id', $user->id)->update(['name' => $name]);
        }

        // Drop new columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'surname']);
        });
    }
};
