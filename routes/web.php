<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\StudentController;
use App\Http\Controllers\Web\OrganizerController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\ImportController;
use App\Http\Controllers\Web\StudentDepartmentController;
use App\Http\Controllers\Web\ApprovalController;
use App\Http\Controllers\Web\AnalyticsController;
use App\Http\Controllers\Web\FinancialController;
use App\Http\Controllers\Web\ProposalController;
use App\Http\Controllers\Web\PredictiveAnalyticsController;

// ── Public Routes ────────────────────────────────────────────────────────────
Route::get('/seed-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

        // 1. System Admin
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@nu-clark.edu.ph'],
            [
                'first_name' => 'System',
                'surname'    => 'Administrator',
                'password'   => \Illuminate\Support\Facades\Hash::make('Password123@'),
                'role'       => 'admin',
                'is_active'  => true,
            ]
        );

        // 2. Organizers
        $org1 = \App\Models\User::updateOrCreate(['email' => 'organizer@nu-clark.edu.ph'], [
            'first_name'  => 'Maria',
            'surname'     => 'Santos',
            'password'    => \Illuminate\Support\Facades\Hash::make('password'),
            'role'        => 'organizer',
            'is_active'   => true,
        ]);

        $org2 = \App\Models\User::updateOrCreate(['email' => 'faculty@nu-clark.edu.ph'], [
            'first_name'  => 'Juan',
            'surname'     => 'Cruz',
            'password'    => \Illuminate\Support\Facades\Hash::make('password'),
            'role'        => 'organizer',
            'is_active'   => true,
        ]);

        // 3. Approvers
        $approvers = [
            ['first_name' => 'Arnell',       'surname' => 'Diego',    'email' => 'exec@nu-clark.edu.ph',    'role' => 'executive_director'],
            ['first_name' => 'Ronielle',     'surname' => 'Antonio',  'email' => 'chair@nu-clark.edu.ph',   'role' => 'program_chair'],
            ['first_name' => 'Rafaela Mae',  'surname' => 'Landayan', 'email' => 'dean@nu-clark.edu.ph',    'role' => 'dean'],
            ['first_name' => 'Vernie',       'surname' => 'Garcia',   'email' => 'studdev@nu-clark.edu.ph', 'role' => 'student_development'],
            ['first_name' => 'BSIT',         'surname' => 'Rep',      'email' => 'bsit@nu-clark.edu.ph',    'role' => 'student_department'],
            ['first_name' => 'BSBA',         'surname' => 'Rep',      'email' => 'bsba@nu-clark.edu.ph',    'role' => 'student_department']
        ];
        foreach ($approvers as $appr) {
            \App\Models\User::updateOrCreate(
                ['email' => $appr['email']],
                array_merge($appr, ['password' => \Illuminate\Support\Facades\Hash::make('password'), 'is_active' => true])
            );
        }

        // 4. Courses
        $courses = [
            ['code' => 'BSIT',     'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSIT-MWA', 'name' => 'BS in Information Technology (MWA)'],
            ['code' => 'BSA',      'name' => 'Bachelor of Science in Accountancy'],
            ['code' => 'BSTM',     'name' => 'Bachelor of Science in Tourism Management'],
            ['code' => 'BSP',      'name' => 'Bachelor of Science in Psychology'],
            ['code' => 'BACOMM',   'name' => 'Bachelor of Arts in Communication'],
            ['code' => 'BAPOLSCI', 'name' => 'Bachelor of Arts in Political Science'],
            ['code' => 'BSCPE',    'name' => 'Bachelor of Science in Computer Engineering'],
            ['code' => 'BSCE',     'name' => 'Bachelor of Science in Civil Engineering'],
            ['code' => 'BSMA',     'name' => 'Bachelor of Science in Management Accounting'],
            ['code' => 'BSBA-MM',  'name' => 'Bachelor of Science in Business Administration'],
            ['code' => 'BSARCH',   'name' => 'Bachelor of Science in Architecture'],
        ];
        foreach ($courses as $c) {
            \App\Models\Course::updateOrCreate(['code' => $c['code']], array_merge($c, ['is_active' => true]));
        }

        $bsit = \App\Models\Course::where('code', 'BSIT')->first();

        // 5. Sections
        $sec = \App\Models\Section::updateOrCreate(
            ['name' => 'ITE-201'],
            ['course_id' => $bsit ? $bsit->id : 1, 'year_level' => 2, 'is_active' => true]
        );

        // 6. Sample Students
        $students = [
            ['first_name' => 'Ana',    'surname' => 'Reyes',    'email' => 'ana.reyes@students.nu-clark.edu.ph',      'student_id' => '2022-00001'],
            ['first_name' => 'Carlos', 'surname' => 'Bautista', 'email' => 'carlos.bautista@students.nu-clark.edu.ph', 'student_id' => '2022-00002'],
            ['first_name' => 'Maria',  'surname' => 'Garcia',   'email' => 'maria.garcia@students.nu-clark.edu.ph',    'student_id' => '2022-00003'],
            ['first_name' => 'John',   'surname' => 'Mendoza',  'email' => 'john.mendoza@students.nu-clark.edu.ph',    'student_id' => '2022-00004'],
            ['first_name' => 'Sofia',  'surname' => 'Cruz',     'email' => 'sofia.cruz@students.nu-clark.edu.ph',      'student_id' => '2022-00005'],
        ];
        foreach ($students as $sd) {
            \App\Models\User::updateOrCreate(['email' => $sd['email']], [
                ...$sd,
                'password'   => \Illuminate\Support\Facades\Hash::make('password'),
                'role'       => 'student',
                'course_id'  => $bsit ? $bsit->id : null,
                'section_id' => $sec ? $sec->id : null,
                'is_active'  => true,
            ]);
        }

        // 7. Sample Events
        $events = [
            [
                'title'       => 'NU Clark Tech Expo & QR Summit 2026 (LIVE DEMO)',
                'description' => 'Official technology showcase and live event management demo for National University Clark. Experience the live QR two-scan In & Out attendance system.',
                'venue'       => 'NU Clark Auditorium',
                'event_date'  => now()->toDateString(),
                'start_time'  => '08:00',
                'end_time'    => '20:00',
                'capacity'    => 350,
                'category'    => 'Academic',
                'is_featured' => true,
                'status'      => 'published',
                'organizer_id'=> $org1->id,
            ],
            [
                'title'       => 'NU Clark Acquaintance Party 2026',
                'description' => 'Annual acquaintance party for all freshmen students of National University Clark.',
                'venue'       => 'NU Clark Gymnasium',
                'event_date'  => now()->addDays(5)->toDateString(),
                'start_time'  => '10:00',
                'end_time'    => '17:00',
                'capacity'    => 500,
                'category'    => 'Social',
                'is_featured' => true,
                'status'      => 'published',
                'organizer_id'=> $org1->id,
            ]
        ];
        foreach ($events as $ev) {
            \App\Models\Event::updateOrCreate(['title' => $ev['title']], $ev);
        }

        return redirect()->route('login')->with('success', 'Database seeded successfully with all organizers, approvers, students, courses, and events!');
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
            'trace' => $e->getFile() . ':' . $e->getLine(),
        ], 500);
    }
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [HomeController::class, 'events'])->name('events');
Route::get('/events/{id}', [HomeController::class, 'showEvent'])->name('event.show');
Route::get('/calendar/events/json', [HomeController::class, 'calendarEventsJson'])->name('calendar.events.json');
// S3 Storage Proxy (Universal Fallback for Private/Strict Buckets)
Route::get('/storage/s3/{path}', function ($path) {
    // Prevent path traversal
    if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
        abort(403, 'Invalid file path.');
    }
    // Protect sensitive directories — require authentication
    if (str_starts_with($path, 'signatures/') && !auth()->check()) {
        abort(403, 'Unauthorized access to signatures.');
    }
    // Check local public disk first
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        $content = \Illuminate\Support\Facades\Storage::disk('public')->get($path);
        $mime = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($path);
        return response($content, 200)->header('Content-Type', $mime);
    }
    try {
        $disk = \Illuminate\Support\Facades\Storage::disk('s3');
        
        if (!$disk->exists($path)) {
            \Illuminate\Support\Facades\Log::warning("S3 Proxy: File not found at path: " . $path);
            abort(404, "File not found in storage.");
        }

        $content = $disk->get($path);
        $mime = $disk->mimeType($path);
        
        return response($content, 200)->header('Content-Type', $mime);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("S3 Proxy Error for path [{$path}]: " . $e->getMessage());
        return response("Error accessing storage.", 500);
    }
})->where('path', '.*')->name('storage.s3');

// Admin-only Debug & Diagnostic Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/test-s3', function () {
        try {
            \Illuminate\Support\Facades\Storage::disk('s3')->put('test.txt', 'hello');
            $exists = \Illuminate\Support\Facades\Storage::disk('s3')->exists('test.txt');
            return "S3 Connection Success! File saved! Exists: " . ($exists ? 'Yes' : 'No');
        } catch (\Exception $e) {
            return "S3 Connection Failed: " . $e->getMessage();
        }
    });

    Route::get('/test-sig', function() {
        $u = \App\Models\User::where('role', 'student_development')->first();
        if (!$u) return "User not found";
        $path = $u->e_signature_path;
        $url = \App\Helpers\StorageUrl::url($path);
        $exists = \Illuminate\Support\Facades\Storage::disk('s3')->exists($path);
        return "Path: " . $path . "<br>URL: " . $url . "<br>Exists in S3: " . ($exists ? 'Yes' : 'No');
    });

    Route::get('/check-pending', function () {
        $pending = \App\Models\Attendance::with(['registration.user', 'registration.event'])
            ->where('status', 'pending')
            ->get();
        
        return response()->json([
            'server_time' => now()->toDateTimeString(),
            'manila_time' => now('Asia/Manila')->toDateTimeString(),
            'pending_count' => $pending->count(),
            'records' => $pending
        ]);
    });
});

// Force sync database (Admin-only fallback for Railway)
Route::post('/force-sync-database', function () {
    if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
        abort(403, 'Admin access required.');
    }
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    return redirect('/')->with('success', 'Database forced sync successful!');
})->middleware(['auth', 'role:admin']);

// ── Auth Routes (guests only) ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', function () {
        return redirect()->route('login')->with('error', 'Public registration is disabled. Student accounts are imported by administrators.');
    })->name('register');
    Route::get('/forgot-password',  [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email')->middleware('throttle:forgot-password');
    Route::get('/reset-password',   [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Shared Authenticated Routes ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/notifications/mark-read', function () {
        \App\Models\AppNotification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.markRead');

    // Event Proposals
    Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals.index');
    Route::get('/proposals/create/{eventId}', [ProposalController::class, 'create'])->name('proposal.create');
    Route::post('/proposals', [ProposalController::class, 'store'])->name('proposal.store');
    Route::get('/proposals/{id}', [ProposalController::class, 'show'])->name('proposal.show');
    Route::post('/proposals/{id}/submit', [ProposalController::class, 'submit'])->name('proposal.submit');
    Route::post('/proposals/{id}/approve', [ProposalController::class, 'approve'])->name('proposal.approve');
    Route::post('/proposals/{id}/reject', [ProposalController::class, 'reject'])->name('proposal.reject');
    Route::get('/proposals/{id}/export-pdf', [ProposalController::class, 'exportPdf'])->name('proposal.export-pdf');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendVerificationCode'])->name('verification.resend');
    Route::get('/verify-email/resend', fn() => redirect()->route('verification.notice'));
});

// Student
Route::middleware(['auth', 'role:student,admin', 'email.verified'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard',     [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/events',        [StudentController::class, 'events'])->name('events');
    Route::get('/my-events',     [StudentController::class, 'myEvents'])->name('my-events');
    Route::get('/qr/{id}',       [StudentController::class, 'qrCode'])->name('qr');
    Route::get('/qr/{id}/token', [StudentController::class, 'getQrToken'])->name('qr.token');
    Route::get('/history',       [StudentController::class, 'history'])->name('history');
    Route::get('/profile',       [StudentController::class, 'profile'])->name('profile');
    Route::post('/events/{id}/register', [StudentController::class, 'register'])->name('register');
    Route::delete('/registration/{id}',  [StudentController::class, 'cancel'])->name('cancel');
    Route::post('/attendance/checkin',   [StudentController::class, 'checkin'])->name('checkin');
    Route::post('/attendance/checkout/{id}', [StudentController::class, 'checkout'])->name('checkout');
});

// Organizer & Student Development
Route::middleware(['auth', 'role:organizer,student_development,admin'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/dashboard',           [OrganizerController::class, 'dashboard'])->name('dashboard');
    Route::get('/events',              [OrganizerController::class, 'events'])->name('events');
    Route::get('/events/create',       [OrganizerController::class, 'createEvent'])->name('event.create');
    Route::post('/events',             [OrganizerController::class, 'storeEvent'])->name('event.store');
    Route::get('/events/{id}/edit',    [OrganizerController::class, 'editEvent'])->name('event.edit');
    Route::put('/events/{id}',         [OrganizerController::class, 'updateEvent'])->name('event.update');
    Route::delete('/events/{id}',      [OrganizerController::class, 'deleteEvent'])->name('event.delete');
    Route::get('/events/{id}/attendees', [OrganizerController::class, 'attendees'])->name('event.attendees');
    Route::put('/attendance/{id}/verify',[OrganizerController::class, 'verify'])->name('attendance.verify');
    Route::get('/events/{id}/attendance/export-pdf',   [OrganizerController::class, 'exportPdf'])->name('attendance.pdf');
    Route::get('/events/{id}/attendance/export-excel', [OrganizerController::class, 'exportExcel'])->name('attendance.excel');
    Route::get('/attendees',              [OrganizerController::class, 'allAttendees'])->name('attendees');
    Route::get('/analytics',           [OrganizerController::class, 'analytics'])->name('analytics');
    Route::get('/scan/{token}',        [OrganizerController::class, 'scanQr'])->name('scan');
});

// Student Department (Venue Reservations)
Route::middleware(['auth', 'role:student_department,admin'])->prefix('student-department')->name('student_department.')->group(function () {
    Route::get('/dashboard',                [StudentDepartmentController::class, 'dashboard'])->name('dashboard');
    Route::get('/venue-reservations/availability', [StudentDepartmentController::class, 'checkAvailability'])->name('venue.availability');
    Route::get('/venue-reservations/events',[StudentDepartmentController::class, 'calendarEvents'])->name('venue.events.json');
    Route::post('/venue-reservations',      [StudentDepartmentController::class, 'storeVenueReservation'])->name('venue.store');
    Route::delete('/venue-reservations/{id}',[StudentDepartmentController::class, 'deleteVenueReservation'])->name('venue.delete');
    Route::get('/venue-reservations/{id}/form',[StudentDepartmentController::class, 'showPermissionForm'])->name('venue.form');
    Route::post('/signature',               [StudentDepartmentController::class, 'uploadSignature'])->name('signature.upload');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',         [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/audit-logs',        [AdminController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/users',             [AdminController::class, 'users'])->name('users');
    Route::post('/users',            [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{id}',        [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}',     [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{id}/verify-email', [AdminController::class, 'verifyUserEmail'])->name('users.verify-email');
    Route::get('/courses',           [AdminController::class, 'courses'])->name('courses');
    Route::post('/courses',          [AdminController::class, 'storeCourse'])->name('courses.store');
    Route::get('/reports',           [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/events/pdf',[AdminController::class, 'exportEventsPdf'])->name('reports.events.pdf');
    Route::get('/analytics',         [AnalyticsController::class, 'dashboard'])->name('analytics');
    Route::get('/analytics/export-pdf', [AnalyticsController::class, 'exportPdf'])->name('analytics.export-pdf');
    Route::get('/analytics/student/{id}', [AnalyticsController::class, 'studentProfile'])->name('analytics.student');
    Route::get('/notifications',     [AdminController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/send',[AdminController::class, 'sendNotification'])->name('notifications.send');
    
    // Event Management (Admin)
    Route::get('/events',            [AdminController::class, 'events'])->name('events');
    Route::delete('/events/{id}',    [AdminController::class, 'deleteEvent'])->name('events.delete');
    Route::put('/events/{id}/status',[AdminController::class, 'updateEventStatus'])->name('events.status');
    // Venue management
    Route::get('/venues',            [AdminController::class, 'venues'])->name('venues');
    Route::put('/venues/{id}/status',[AdminController::class, 'updateVenueStatus'])->name('venues.status');
    Route::delete('/venues/{id}',    [AdminController::class, 'deleteVenue'])->name('venues.delete');
    Route::post('/venues/{id}/override', [AdminController::class, 'overrideVenue'])->name('venues.override');
    
    // File Hunting Management
    Route::get('/file-hunting',      [AdminController::class, 'fileHunting'])->name('file-hunting');
    Route::post('/file-hunting',     [AdminController::class, 'saveSignatories'])->name('file-hunting.save');
    
    // CSV Import
    Route::get('/import',              [ImportController::class, 'showImport'])->name('import');
    Route::get('/import/template',     [ImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/import',             [ImportController::class, 'importStudents'])->name('import.run');

    // Financial Management
    Route::get('/financial', [FinancialController::class, 'dashboard'])->name('financial');
    Route::get('/financial/dashboard', [FinancialController::class, 'dashboard'])->name('financial.dashboard');
    Route::get('/financial/event/{id}/budget', [FinancialController::class, 'eventBudget'])->name('event.budget');
    Route::get('/financial/budget/{id}', [FinancialController::class, 'eventBudget'])->name('financial.budget');
    Route::post('/financial/event/{id}/budget', [FinancialController::class, 'storeBudgetItem'])->name('event.budget.store');
    Route::put('/financial/budget/{id}', [FinancialController::class, 'updateBudgetItem'])->name('budget.update');
    Route::delete('/financial/budget/{id}', [FinancialController::class, 'deleteBudgetItem'])->name('budget.delete');
    Route::get('/financial/event/{id}/payments', [FinancialController::class, 'eventPayments'])->name('event.payments');
    Route::get('/financial/payments/{id}', [FinancialController::class, 'eventPayments'])->name('financial.payments');
    Route::post('/financial/event/{id}/payments', [FinancialController::class, 'storePayment'])->name('event.payments.store');
    Route::delete('/financial/payment/{id}', [FinancialController::class, 'deletePayment'])->name('payment.delete');
    Route::get('/financial/event/{id}/export-pdf', [FinancialController::class, 'exportPdf'])->name('financial.export-pdf');

    // Predictive Analytics, Scheduling Optimization & Resource Planning
    Route::get('/predictive', [PredictiveAnalyticsController::class, 'dashboard'])->name('predictive');
    Route::get('/predictive/event/{id}', [PredictiveAnalyticsController::class, 'eventPrediction'])->name('predictive.event');
    Route::get('/predictive/schedule-optimizer', [PredictiveAnalyticsController::class, 'scheduleOptimizer'])->name('predictive.schedule');
    Route::get('/predictive/resource-planner/{id}', [PredictiveAnalyticsController::class, 'resourcePlanner'])->name('predictive.resource');
    Route::get('/predictive/export-pdf', [PredictiveAnalyticsController::class, 'exportPdf'])->name('predictive.export-pdf');
});

// Approver Flow

Route::middleware(['auth', 'role:adviser,department_head,dean,executive_director,student_development,program_chair,admin'])->prefix('approver')->name('approver.')->group(function () {
    Route::get('/dashboard',              [ApprovalController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile',                [ApprovalController::class, 'profile'])->name('profile');
    Route::post('/profile/update',        [ApprovalController::class, 'updateProfile'])->name('profile.update');
    Route::post('/events/{id}/approve',   [ApprovalController::class, 'approveEvent'])->name('events.approve');
    Route::post('/events/{id}/reject',    [ApprovalController::class, 'rejectEvent'])->name('events.reject');
    Route::post('/venues/{id}/open',      [ApprovalController::class, 'openDocument'])->name('venues.open');
    Route::get('/venues/{id}/form',       [ApprovalController::class, 'showPermissionForm'])->name('venues.form');
    Route::post('/venues/{id}/approve',   [ApprovalController::class, 'approveVenue'])->name('venues.approve');
    Route::post('/venues/{id}/reject',    [ApprovalController::class, 'rejectVenue'])->name('venues.reject');
});

