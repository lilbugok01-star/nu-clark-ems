<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ── System Admin FIRST ──────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@nu-clark.edu.ph'],
            [
                'first_name' => 'System',
                'surname'    => 'Administrator',
                'password'   => Hash::make('Password123@'),
                'role'       => 'admin',
                'is_active'  => true,
            ]
        );

        // 2. ── Approvers & Department Staff ─────────────────────
        $this->call(VenueApproversSeeder::class);

        // 3. ── Organizers ───────────────────────────────────────
        $org1 = User::firstOrCreate(['email' => 'organizer@nu-clark.edu.ph'], [
            'first_name'  => 'Maria',
            'middle_name' => null,
            'surname'     => 'Santos',
            'password' => Hash::make('password'),
            'role'     => 'organizer',
        ]);

        $org2 = User::firstOrCreate(['email' => 'faculty@nu-clark.edu.ph'], [
            'first_name'  => 'Juan',
            'middle_name' => 'dela',
            'surname'     => 'Cruz',
            'password' => Hash::make('password'),
            'role'     => 'organizer',
        ]);

        // 4. ── Courses ──────────────────────────────────────────
        $courses = [
            ['code' => 'BSIT-MWA', 'name' => 'Bachelor of Science in Information Technology with specialization in Mobile and Web Applications'],
            ['code' => 'BSA',      'name' => 'Bachelor of Science in Accountancy'],
            ['code' => 'BSTM',     'name' => 'Bachelor of Science in Tourism Management'],
            ['code' => 'BSIT',     'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSP',      'name' => 'Bachelor of Science in Psychology'],
            ['code' => 'BACOMM',   'name' => 'Bachelor of Arts in Communication'],
            ['code' => 'BAPOLSCI', 'name' => 'Bachelor of Arts in Political Science'],
            ['code' => 'BSCPE',    'name' => 'Bachelor of Science in Computer Engineering'],
            ['code' => 'BSCE',     'name' => 'Bachelor of Science in Civil Engineering'],
            ['code' => 'BSMA',     'name' => 'Bachelor of Science in Management Accounting'],
            ['code' => 'BSBA-MM',  'name' => 'Bachelor of Science in Business Administration Major in Marketing Management'],
            ['code' => 'BSARCH',   'name' => 'Bachelor of Science in Architecture'],
        ];

        foreach ($courses as $c) {
            Course::updateOrCreate(
                ['code' => $c['code']],
                array_merge($c, ['is_active' => true])
            );
        }

        // 5. ── Sections (Top sections for sample students) ──────
        $courseMap = [
            'BSIT-MWA' => ['prefix' => 'MWA', 'years' => 4, 'sections_per_year' => 3],
            'BSA'      => ['prefix' => 'ACC', 'years' => 4, 'sections_per_year' => 3],
            'BSTM'     => ['prefix' => 'TRM', 'years' => 4, 'sections_per_year' => 3],
            'BSIT'     => ['prefix' => 'ITE', 'years' => 4, 'sections_per_year' => 3],
            'BSP'      => ['prefix' => 'PSY', 'years' => 4, 'sections_per_year' => 3],
            'BACOMM'   => ['prefix' => 'COM', 'years' => 4, 'sections_per_year' => 3],
            'BAPOLSCI' => ['prefix' => 'POL', 'years' => 4, 'sections_per_year' => 3],
            'BSCPE'    => ['prefix' => 'CPE', 'years' => 4, 'sections_per_year' => 3],
            'BSCE'     => ['prefix' => 'CVE', 'years' => 4, 'sections_per_year' => 3],
            'BSMA'     => ['prefix' => 'MAC', 'years' => 4, 'sections_per_year' => 3],
            'BSBA-MM'  => ['prefix' => 'MKT', 'years' => 4, 'sections_per_year' => 3],
            'BSARCH'   => ['prefix' => 'ARE', 'years' => 4, 'sections_per_year' => 3],
        ];

        foreach ($courseMap as $code => $config) {
            $course = Course::where('code', $code)->first();
            if (!$course) continue;
            for ($year = 1; $year <= $config['years']; $year++) {
                for ($sec = 1; $sec <= $config['sections_per_year']; $sec++) {
                    $sectionName = $config['prefix'] . '-' . $year . str_pad($sec, 2, '0', STR_PAD_LEFT);
                    Section::updateOrCreate(
                        ['course_id' => $course->id, 'name' => $sectionName],
                        ['year_level' => $year, 'is_active' => true]
                    );
                }
            }
        }

        // 6. ── Sample Students ──────────────────────────────────
        $bsitCourse  = Course::where('code', 'BSIT')->first();
        $iteSection  = Section::where('name', 'ITE-201')->first();

        $studentData = [
            ['first_name' => 'Ana',    'surname' => 'Reyes',    'email' => 'ana.reyes@students.nu-clark.edu.ph',      'student_id' => '2022-00001'],
            ['first_name' => 'Carlos', 'surname' => 'Bautista', 'email' => 'carlos.bautista@students.nu-clark.edu.ph', 'student_id' => '2022-00002'],
            ['first_name' => 'Maria',  'surname' => 'Garcia',   'email' => 'maria.garcia@students.nu-clark.edu.ph',    'student_id' => '2022-00003'],
            ['first_name' => 'John',   'surname' => 'Mendoza',  'email' => 'john.mendoza@students.nu-clark.edu.ph',    'student_id' => '2022-00004'],
            ['first_name' => 'Sofia',  'surname' => 'Cruz',     'email' => 'sofia.cruz@students.nu-clark.edu.ph',      'student_id' => '2022-00005'],
        ];

        foreach ($studentData as $sd) {
            User::firstOrCreate(['email' => $sd['email']], [
                ...$sd,
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'course_id'  => $bsitCourse ? $bsitCourse->id : null,
                'section_id' => $iteSection ? $iteSection->id : null,
                'is_active'  => true,
            ]);
        }

        // ── Clean up any dummy test events ───────
        Event::where('title', 'like', '%System Test Event%')->delete();

        // ── Sample Events (2026 Academic Year) ───
        $events = [
            [
                'title'       => 'NU Clark Tech Expo & QR Summit 2026 (LIVE DEMO)',
                'description' => 'Official technology showcase and live event management demo for National University Clark. Experience the live QR two-scan In & Out attendance system.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->toDateString(), // LIVE TODAY for instant defense demo
                'start_time'  => '08:00',
                'end_time'    => '20:00',
                'capacity'    => 350,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'NU Clark Acquaintance Party 2026',
                'description' => 'Annual acquaintance party for all incoming freshmen students of National University Clark. Meet your professors, upperclassmen, and fellow new students.',
                'venue'       => 'NU Clark Gymnasium',
                'event_date'  => now()->addDays(5)->toDateString(),
                'start_time'  => '10:00',
                'end_time'    => '17:00',
                'capacity'    => 500,
                'category'    => 'Social',
                'is_featured' => true,
            ],
            [
                'title'       => 'IT Summit 2026: Future of AI & Cloud Systems',
                'description' => 'Join us for a full-day technology summit featuring speakers from the industry on the future of Artificial Intelligence, Machine Learning, and emerging technologies.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->addDays(12)->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '17:00',
                'capacity'    => 300,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'Leadership and Values Formation Seminar',
                'description' => 'A values formation and leadership seminar for all student government officers and student leaders.',
                'venue'       => 'NU Clark Function Hall',
                'event_date'  => now()->addDays(7)->toDateString(),
                'start_time'  => '09:00',
                'end_time'    => '16:00',
                'capacity'    => 150,
                'category'    => 'Leadership',
                'is_featured' => false,
            ],
            [
                'title'       => 'NU Clark Sports Festival 2026',
                'description' => 'Annual intramural sports competition open to all departments. Compete in basketball, volleyball, badminton, and more!',
                'venue'       => 'NU Clark Sports Complex',
                'event_date'  => now()->addDays(20)->toDateString(),
                'start_time'  => '07:00',
                'end_time'    => '18:00',
                'capacity'    => 1000,
                'category'    => 'Sports',
                'is_featured' => true,
            ],
            [
                'title'       => 'Research Colloquium: Capstone Defense Preparation',
                'description' => 'Workshops and mock presentations to help graduating students prepare for their capstone project defense.',
                'venue'       => 'NU Clark Research Center',
                'event_date'  => now()->addDays(15)->toDateString(),
                'start_time'  => '13:00',
                'end_time'    => '17:00',
                'capacity'    => 200,
                'category'    => 'Academic',
                'is_featured' => false,
            ],
            [
                'title'       => 'Seminar on Cyber Security & Ethical Hacking',
                'description' => 'A seminar discussing the latest trends in cybersecurity and ethical hacking.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->addDays(18)->toDateString(),
                'start_time'  => '13:00',
                'end_time'    => '16:00',
                'capacity'    => 200,
                'category'    => 'Academic',
                'is_featured' => false,
            ],
            [
                'title'       => 'NU Clark Foundation Day 2026',
                'description' => 'Celebrate the founding anniversary of NU Clark with food, music, and activities.',
                'venue'       => 'NU Clark Open Grounds',
                'event_date'  => now()->addDays(30)->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '20:00',
                'capacity'    => 2000,
                'category'    => 'Social',
                'is_featured' => true,
            ],
            [
                'title'       => 'Job Fair 2026',
                'description' => 'Annual career and job fair with top companies in the region offering internships and fresh grad roles.',
                'venue'       => 'NU Clark Function Hall',
                'event_date'  => now()->addDays(25)->toDateString(),
                'start_time'  => '09:00',
                'end_time'    => '17:00',
                'capacity'    => 1000,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'Hackathon 2026: Code for Good',
                'description' => 'A 24-hour coding competition where students build software solutions for social issues.',
                'venue'       => 'IT Laboratories 1-3',
                'event_date'  => now()->addDays(35)->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '17:00',
                'capacity'    => 150,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
        ];

        $students = User::where('role', 'student')->get();

        foreach ($events as $index => $eData) {
            $event = Event::updateOrCreate(
                ['title' => $eData['title']],
                [...$eData, 'organizer_id' => $org1->id, 'status' => 'published']
            );

            // Register students for demo events
            foreach ($students as $sIndex => $student) {
                $reg = Registration::firstOrCreate(
                    ['user_id' => $student->id, 'event_id' => $event->id],
                    [
                        'qr_token'      => Registration::generateQrToken($student->id, $event->id),
                        'qr_expires_at' => \Carbon\Carbon::parse($eData['event_date'] . ' ' . substr((string)$event->end_time, 0, 5))->addDay(),
                        'status'        => 'confirmed',
                        'registered_at' => now(),
                    ]
                );

                // For the LIVE event (index 0), seed realistic attendances
                if ($index === 0) {
                    if ($sIndex === 0) {
                        // Student 1: Both Time In and Time Out completed
                        \App\Models\Attendance::updateOrCreate(
                            ['registration_id' => $reg->id],
                            [
                                'checked_in_at'  => now()->subHours(4),
                                'checked_out_at' => now()->subHours(1),
                                'status'         => 'verified',
                                'verified_by'    => $org1->id,
                                'verified_at'    => now()->subHours(4),
                            ]
                        );
                    } elseif ($sIndex === 1) {
                        // Student 2: Time In recorded, Time Out pending (active in event)
                        \App\Models\Attendance::updateOrCreate(
                            ['registration_id' => $reg->id],
                            [
                                'checked_in_at'  => now()->subHours(2),
                                'checked_out_at' => null,
                                'status'         => 'verified',
                                'verified_by'    => $org1->id,
                                'verified_at'    => now()->subHours(2),
                            ]
                        );
                    }
                    // Remaining students: Registered, ready for live scanner demonstration!
                }
            }
        }
    }
}
