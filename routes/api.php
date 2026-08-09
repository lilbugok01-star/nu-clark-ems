<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;

// ─────────────────────────────────────────────
// Public Auth Routes
// ─────────────────────────────────────────────
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// Public event browsing
Route::middleware('throttle:api')->group(function () {
    Route::get('/events',           [EventController::class, 'index']);
    Route::get('/events/upcoming',  [EventController::class, 'upcoming']);
    Route::get('/events/{id}',      [EventController::class, 'show']);
    Route::get('/courses',          [UserController::class, 'courses']);
    Route::get('/sections',         [UserController::class, 'sections']);
});

// ─────────────────────────────────────────────
// Authenticated Routes
// ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/user',     [AuthController::class, 'user']);

    // Email Verification
    Route::post('/verify-email',           [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification',    [AuthController::class, 'resendVerificationCode']);

    // Notifications
    Route::get('/notifications',             [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read',   [NotificationController::class, 'markRead']);
    Route::put('/notifications/read-all',    [NotificationController::class, 'markAllRead']);

    // QR Code — students & organizers
    Route::get('/qrcode/{registration_id}',  [QrCodeController::class, 'generate']);
    Route::get('/qrcode/{registration_id}/info', [QrCodeController::class, 'info']);

    // Attendance check-in (student use)
    Route::post('/attendance/checkin',       [AttendanceController::class, 'checkin']);

    // ─── Student Routes ───────────────────────
    Route::middleware(['role:student,organizer,admin'])->group(function () {
        Route::post('/events/{id}/register',  [RegistrationController::class, 'register']);
        Route::get('/my-registrations',       [RegistrationController::class, 'myRegistrations']);
        Route::delete('/registration/{id}',   [RegistrationController::class, 'cancel']);
        Route::get('/dashboard/student',      [DashboardController::class, 'studentStats']);
    });

    // ─── Organizer + Admin Routes ─────────────
    Route::middleware(['role:organizer,admin'])->group(function () {
        // Event management
        Route::post('/events',              [EventController::class, 'store']);
        Route::put('/events/{id}',          [EventController::class, 'update']);
        Route::delete('/events/{id}',       [EventController::class, 'destroy']);

        // Attendance management
        Route::get('/events/{id}/attendance',   [AttendanceController::class, 'eventAttendance']);
        Route::get('/attendance/{id}',           [AttendanceController::class, 'show']);
        Route::put('/attendance/{id}/verify',    [AttendanceController::class, 'verify']);

        // Dashboard
        Route::get('/dashboard/organizer',      [DashboardController::class, 'organizerStats']);

        // Reports
        Route::get('/reports/events',                           [ReportController::class, 'events']);
        Route::get('/reports/attendance/{event_id}',            [ReportController::class, 'attendance']);
        Route::get('/reports/attendance/{event_id}/pdf',        [ReportController::class, 'exportAttendancePdf']);
        Route::get('/reports/attendance/{event_id}/excel',      [ReportController::class, 'exportAttendanceExcel']);
    });

    // ─── Admin-Only Routes ────────────────────
    Route::middleware(['role:admin'])->group(function () {
        // User management
        Route::get('/users',            [UserController::class, 'index']);
        Route::post('/users',           [UserController::class, 'store']);
        Route::get('/users/{id}',       [UserController::class, 'show']);
        Route::put('/users/{id}',       [UserController::class, 'update']);
        Route::delete('/users/{id}',    [UserController::class, 'destroy']);

        // Notifications broadcast
        Route::post('/notifications/send', [NotificationController::class, 'send']);

        // Dashboard & Reports
        Route::get('/dashboard/stats',  [DashboardController::class, 'stats']);
        Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

        Route::get('/reports/student/{student_id}', [ReportController::class, 'student']);
        Route::get('/reports/events/pdf',           [ReportController::class, 'exportEventsPdf']);

        // Registration detail
        Route::get('/registration/{id}', [RegistrationController::class, 'show']);
    });
});
