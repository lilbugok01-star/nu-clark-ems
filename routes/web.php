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

// ── Public Routes ────────────────────────────────────────────────────────────
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
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:register');
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
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendVerificationCode'])->name('verification.resend');
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
    Route::get('/courses',           [AdminController::class, 'courses'])->name('courses');
    Route::post('/courses',          [AdminController::class, 'storeCourse'])->name('courses.store');
    Route::get('/reports',           [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/events/pdf',[AdminController::class, 'exportEventsPdf'])->name('reports.events.pdf');
    Route::get('/notifications',     [AdminController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/send',[AdminController::class, 'sendNotification'])->name('notifications.send');
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

