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
            ['first_name' => 'Arnell',       'middle_name' => 'A.',  'surname' => 'Diego',    'email' => 'exec@nu-clark.edu.ph',    'role' => 'executive_director'],
            ['first_name' => 'Ronielle',     'middle_name' => 'B.',  'surname' => 'Antonio',  'email' => 'chair@nu-clark.edu.ph',   'role' => 'program_chair'],
            ['first_name' => 'Rafaela Mae',  'middle_name' => 'M.',  'surname' => 'Landayan', 'email' => 'dean@nu-clark.edu.ph',    'role' => 'dean'],
            ['first_name' => 'Vernie',       'middle_name' => 'B',   'surname' => 'Garcia',   'email' => 'studdev@nu-clark.edu.ph', 'role' => 'student_development'],
            ['first_name' => 'BSIT',         'middle_name' => null,  'surname' => 'Representative', 'email' => 'bsit@nu-clark.edu.ph', 'role' => 'student_department'],
            ['first_name' => 'BSBA',         'middle_name' => null,  'surname' => 'Representative', 'email' => 'bsba@nu-clark.edu.ph', 'role' => 'student_department']
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['password' => bcrypt('password')])
            );
        }
    }
}
