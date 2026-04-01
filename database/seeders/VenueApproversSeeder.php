<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class VenueApproversSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Dr. Arnell A. Diego', 'email' => 'exec@nu-clark.edu.ph', 'role' => 'executive_director'],
            ['name' => 'Ronielle B. Antonio', 'email' => 'chair@nu-clark.edu.ph', 'role' => 'program_chair'],
            ['name' => 'Rafaela Mae M. Landayan', 'email' => 'dean@nu-clark.edu.ph', 'role' => 'dean'],
            ['name' => 'Engr Vernie B Garcia', 'email' => 'studdev@nu-clark.edu.ph', 'role' => 'student_development'],
            ['name' => 'BSIT Representative', 'email' => 'bsit@nu-clark.edu.ph', 'role' => 'student_department'],
            ['name' => 'BSBA Representative', 'email' => 'bsba@nu-clark.edu.ph', 'role' => 'student_department']
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['password' => bcrypt('password')])
            );
        }
    }
}
