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
        // ── Courses ──────────────────────────────
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

        // Deactivate all existing courses first
        Course::where('is_active', true)->update(['is_active' => false]);

        foreach ($courses as $c) {
            Course::updateOrCreate(['code' => $c['code']], array_merge($c, ['is_active' => true]));
        }

        // ── Sections ─────────────────────────────
        $courseMap = [
            'BSIT-MWA' => ['prefix' => 'MWA', 'years' => 4, 'sections_per_year' => 5],
            'BSA'      => ['prefix' => 'ACC', 'years' => 4, 'sections_per_year' => 5],
            'BSTM'     => ['prefix' => 'TRM', 'years' => 4, 'sections_per_year' => 5],
            'BSIT'     => ['prefix' => 'ITE', 'years' => 4, 'sections_per_year' => 5],
            'BSP'      => ['prefix' => 'PSY', 'years' => 4, 'sections_per_year' => 5],
            'BACOMM'   => ['prefix' => 'COM', 'years' => 4, 'sections_per_year' => 5],
            'BAPOLSCI' => ['prefix' => 'POL', 'years' => 4, 'sections_per_year' => 5],
            'BSCPE'    => ['prefix' => 'CPE', 'years' => 4, 'sections_per_year' => 5],
            'BSCE'     => ['prefix' => 'CVE', 'years' => 4, 'sections_per_year' => 5],
            'BSMA'     => ['prefix' => 'MAC', 'years' => 4, 'sections_per_year' => 5],
            'BSBA-MM'  => ['prefix' => 'MKT', 'years' => 4, 'sections_per_year' => 5],
            'BSARCH'   => ['prefix' => 'ARE', 'years' => 4, 'sections_per_year' => 5],
        ];

        foreach ($courseMap as $code => $config) {
            $course = Course::where('code', $code)->first();
            if (!$course) continue;
            for ($year = 1; $year <= $config['years']; $year++) {
                for ($sec = 1; $sec <= $config['sections_per_year']; $sec++) {
                    $sectionName = $config['prefix'] . '-' . $year . str_pad($sec, 2, '0', STR_PAD_LEFT);
                    Section::firstOrCreate(
                        ['course_id' => $course->id, 'name' => $sectionName],
                        ['course_id' => $course->id, 'name' => $sectionName, 'year_level' => $year, 'is_active' => true]
                    );
                }
            }
        }

        $admin = User::firstOrCreate(['email' => 'admin@nu-clark.edu.ph'], [
            'name'     => 'System Administrator',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'is_active'=> true,
        ]);

        // Create Approvers/Staff FIRST before doing heavy tasks
        $this->call(VenueApproversSeeder::class);

        // ── Organizers ────────────────────────────
        $org1 = User::firstOrCreate(['email' => 'organizer@nu-clark.edu.ph'], [
            'name'     => 'Dr. Maria Santos',
            'password' => Hash::make('password'),
            'role'     => 'organizer',
        ]);

        $org2 = User::firstOrCreate(['email' => 'faculty@nu-clark.edu.ph'], [
            'name'     => 'Prof. Juan dela Cruz',
            'password' => Hash::make('password'),
            'role'     => 'organizer',
        ]);

        // ── Sample Students ───────────────────────
        $course  = Course::where('code', 'BSIT')->first();
        $section = Section::where('name', 'ITE-201')->first();

        $studentData = [
            ['name' => 'Ana Reyes',      'email' => 'ana.reyes@student.nu-clark.edu.ph',      'student_id' => '2022-00001'],
            ['name' => 'Carlos Bautista','email' => 'carlos.bautista@student.nu-clark.edu.ph', 'student_id' => '2022-00002'],
            ['name' => 'Maria Garcia',   'email' => 'maria.garcia@student.nu-clark.edu.ph',    'student_id' => '2022-00003'],
            ['name' => 'John Mendoza',   'email' => 'john.mendoza@student.nu-clark.edu.ph',    'student_id' => '2022-00004'],
            ['name' => 'Sofia Cruz',     'email' => 'sofia.cruz@student.nu-clark.edu.ph',      'student_id' => '2022-00005'],
        ];

        foreach ($studentData as $sd) {
            User::firstOrCreate(['email' => $sd['email']], [
                ...$sd,
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'course_id'  => $course->id,
                'section_id' => $section->id,
            ]);
        }

        // ── Sample Events ─────────────────────────
        $events = [
            [
                'title'       => 'NU Clark Acquaintance Party 2025',
                'description' => 'Annual acquaintance party for all incoming freshmen students of National University Clark. Meet your professors, upperclassmen, and fellow new students.',
                'venue'       => 'NU Clark Gymnasium',
                'event_date'  => now()->addDays(10)->toDateString(),
                'start_time'  => '10:00',
                'end_time'    => '17:00',
                'capacity'    => 500,
                'category'    => 'Social',
                'is_featured' => true,
            ],
            [
                'title'       => 'IT Summit 2025: Future of AI',
                'description' => 'Join us for a full-day technology summit featuring speakers from the industry on the future of Artificial Intelligence, Machine Learning, and emerging technologies.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->addDays(20)->toDateString(),
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
                'title'       => 'NU Clark Sports Festival 2025',
                'description' => 'Annual intramural sports competition open to all departments. Compete in basketball, volleyball, badminton, and more!',
                'venue'       => 'NU Clark Sports Complex',
                'event_date'  => now()->addDays(30)->toDateString(),
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
                'title'       => 'Seminar on Cyber Security',
                'description' => 'A seminar discussing the latest trends in cybersecurity and ethical hacking.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->addDays(5)->toDateString(),
                'start_time'  => '13:00',
                'end_time'    => '16:00',
                'capacity'    => 200,
                'category'    => 'Academic',
                'is_featured' => false,
            ],
            [
                'title'       => 'NU Clark Foundation Day',
                'description' => 'Celebrate the founding anniversary of NU Clark with food, music, and activities.',
                'venue'       => 'NU Clark Open Grounds',
                'event_date'  => now()->addDays(40)->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '20:00',
                'capacity'    => 2000,
                'category'    => 'Social',
                'is_featured' => true,
            ],
            [
                'title'       => 'Job Fair 2025',
                'description' => 'Annual career and job fair with top companies in the region offering internships and fresh grad roles.',
                'venue'       => 'NU Clark Function Hall',
                'event_date'  => now()->addDays(45)->toDateString(),
                'start_time'  => '09:00',
                'end_time'    => '17:00',
                'capacity'    => 1000,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'Mental Health Awareness Talk',
                'description' => 'A psychological health seminar focusing on stress management for students.',
                'venue'       => 'NU Clark Mini Theater',
                'event_date'  => now()->addDays(2)->toDateString(),
                'start_time'  => '10:00',
                'end_time'    => '12:00',
                'capacity'    => 100,
                'category'    => 'Seminar',
                'is_featured' => false,
            ],
            [
                'title'       => 'Business Plan Pitching Competition',
                'description' => 'BSBA students will pitch their innovative business plans to industry experts.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->addDays(25)->toDateString(),
                'start_time'  => '13:00',
                'end_time'    => '18:00',
                'capacity'    => 250,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'Hackathon 2025: Code for Good',
                'description' => 'A 24-hour coding competition where students build software solutions for social issues.',
                'venue'       => 'IT Laboratories 1-3',
                'event_date'  => now()->addDays(50)->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '17:00',
                'capacity'    => 150,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'Tourism & Hospitality Expo',
                'description' => 'Explore various cuisines and tourism exhibits managed by the BSHM department.',
                'venue'       => 'NU Clark Function Hall',
                'event_date'  => now()->addDays(12)->toDateString(),
                'start_time'  => '10:00',
                'end_time'    => '16:00',
                'capacity'    => 400,
                'category'    => 'Social',
                'is_featured' => false,
            ],
            [
                'title'       => 'Financial Literacy Masterclass',
                'description' => 'Learn personal finance, investing, and wealth building from top financial advisors.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->addDays(8)->toDateString(),
                'start_time'  => '13:00',
                'end_time'    => '16:00',
                'capacity'    => 300,
                'category'    => 'Seminar',
                'is_featured' => false,
            ],
            [
                'title'       => 'Nursing Capping & Pinning Ceremony',
                'description' => 'The traditional ceremony honoring our nursing students before their clinical deployment.',
                'venue'       => 'NU Clark Gymnasium',
                'event_date'  => now()->addDays(55)->toDateString(),
                'start_time'  => '14:00',
                'end_time'    => '18:00',
                'capacity'    => 800,
                'category'    => 'Academic',
                'is_featured' => true,
            ],
            [
                'title'       => 'English Proficiency Workshop',
                'description' => 'A free workshop to enhance conversational and academic English skills.',
                'venue'       => 'Room 302',
                'event_date'  => now()->addDays(14)->toDateString(),
                'start_time'  => '15:00',
                'end_time'    => '17:00',
                'capacity'    => 50,
                'category'    => 'Academic',
                'is_featured' => false,
            ],
            [
                'title'       => 'Architecture Model Exhibit',
                'description' => 'A showcase of scale models and design projects by architecture students.',
                'venue'       => 'Main Lobby',
                'event_date'  => now()->addDays(22)->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '17:00',
                'capacity'    => 300,
                'category'    => 'Arts',
                'is_featured' => false,
            ],
            [
                'title'       => 'Student Leaders Congress',
                'description' => 'A gathering of all organization officers to draft the student activity calendar.',
                'venue'       => 'NU Clark Conference Room',
                'event_date'  => now()->addDays(6)->toDateString(),
                'start_time'  => '09:00',
                'end_time'    => '12:00',
                'capacity'    => 60,
                'category'    => 'Leadership',
                'is_featured' => false,
            ],
            [
                'title'       => 'E-Sports Tournament: Valorant',
                'description' => 'The official inter-department Valorant tournament. Watch the best gamers compete!',
                'venue'       => 'NU Clark Mini Theater',
                'event_date'  => now()->addDays(18)->toDateString(),
                'start_time'  => '10:00',
                'end_time'    => '18:00',
                'capacity'    => 200,
                'category'    => 'Sports',
                'is_featured' => true,
            ],
            [
                'title'       => 'Environmental Awareness Drive',
                'description' => 'Join the green revolution. Seminar followed by a tree-planting activity.',
                'venue'       => 'NU Clark Open Grounds',
                'event_date'  => now()->addDays(35)->toDateString(),
                'start_time'  => '07:00',
                'end_time'    => '12:00',
                'capacity'    => 500,
                'category'    => 'Social',
                'is_featured' => false,
            ],
            [
                'title'       => 'NU Clark Got Talent',
                'description' => 'The ultimate talent search for singing, dancing, and unique performances.',
                'venue'       => 'NU Clark Gymnasium',
                'event_date'  => now()->addDays(60)->toDateString(),
                'start_time'  => '18:00',
                'end_time'    => '22:00',
                'capacity'    => 1000,
                'category'    => 'Arts',
                'is_featured' => true,
            ],
        ];

        foreach ($events as $eData) {
            $event = Event::firstOrCreate(
                ['title' => $eData['title']],
                [...$eData, 'organizer_id' => $org1->id, 'status' => 'published']
            );

            // Register a few students for the first 2 events
            if (in_array($event->title, ['NU Clark Acquaintance Party 2025', 'IT Summit 2025: Future of AI'])) {
                $students = User::where('role', 'student')->get();
                foreach ($students as $student) {
                    if (!Registration::where('user_id', $student->id)->where('event_id', $event->id)->exists()) {
                        Registration::create([
                            'user_id'       => $student->id,
                            'event_id'      => $event->id,
                            'qr_token'      => Registration::generateQrToken($student->id, $event->id),
                            'qr_expires_at' => \Carbon\Carbon::parse($eData['event_date'] . ' ' . substr((string)$event->end_time, 0, 5))->addDay(),
                            'status'        => 'confirmed',
                            'registered_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
