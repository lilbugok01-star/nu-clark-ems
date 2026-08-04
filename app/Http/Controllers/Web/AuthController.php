<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\AppNotification;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()  { return view('auth.login'); }
    
    public function showRegister() {
        $courses  = Course::where('is_active', true)->orderBy('code')->get();
        $sections = Section::where('is_active', true)->with('course')->orderBy('name')->get();
        return view('auth.register', compact('courses', 'sections'));
    }
    
    public function showForgot()   { return view('auth.forgot-password'); }

    public function login(LoginRequest $request)
    {
        $v = $request->validated();
        
        $remember = $request->filled('remember');

        if (!Auth::attempt(['email' => $v['email'], 'password' => $v['password']], $remember)) {
            // Log failed login attempt
            User::log('failed_login_attempt', null, null, ['email' => $v['email']]);
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            User::log('inactive_login_prevented', $user);
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // Log successful login
        User::log('login', $user);

        return match ($user->role) {
            'admin'              => redirect()->route('admin.dashboard'),
            'organizer'          => redirect()->route('organizer.dashboard'),
            'student_department' => redirect()->route('student_department.dashboard'),
            'student'            => redirect()->route('student.dashboard'),
            default              => redirect()->route('approver.dashboard'),
        };
    }

    public function register(RegisterRequest $request)
    {
        $v = $request->validated();

        // Generate dynamic verification code
        $code = sprintf("%06d", mt_rand(100000, 999999));

        $user = User::create([
            'name'                     => $v['name'],
            'email'                    => $v['email'],
            'password'                 => Hash::make($v['password']),
            'student_id'               => $v['student_id'],
            'course_id'                => $v['course_id'],
            'section_id'               => $v['section_id'],
            'email_verification_code'  => $code,
            'role'                     => 'student',
        ]);

        // Log register activity
        User::log('register', $user);

        AppNotification::create([
            'user_id' => $user->id,
            'type'    => 'welcome',
            'title'   => 'Welcome to NU Clark Events!',
            'message' => "Hi {$user->name}, your account has been created. Please verify your email using code: {$code}.",
        ]);

        Auth::login($user);
        
        return redirect()->route('verification.notice')
            ->with('success', 'Verification code generated! Please check your mailbox.');
    }

    public function showVerificationNotice()
    {
        $user = Auth::user();
        if ($user->email_verified_at) {
            return redirect()->route('student.dashboard');
        }
        return view('auth.verification-notice', [
            'email' => $user->email, 
            'code'  => $user->email_verification_code
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if ($user->email_verification_code === $request->code) {
            $user->update([
                'email_verified_at'       => now(),
                'email_verification_code' => null,
            ]);

            User::log('verify_email', $user);

            return redirect()->route('student.dashboard')
                ->with('success', 'Email verified successfully! Welcome to your dashboard.');
        }

        return back()->withErrors(['code' => 'The verification code is invalid.'])->withInput();
    }

    public function resendVerificationCode(Request $request)
    {
        $user = Auth::user();
        $code = sprintf("%06d", mt_rand(100000, 999999));
        
        $user->update([
            'email_verification_code' => $code
        ]);

        User::log('resend_verification_code', $user);

        return back()->with('success', 'A new verification code has been generated. Check the sandbox mailbox!');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        User::log('request_password_reset', $user);

        $resetLink = route('password.reset', ['token' => $token, 'email' => $request->email]);

        return back()->with('success', 'Password reset link generated!')
            ->with('reset_link', $resetLink);
    }

    public function showReset(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $v = $request->validated();

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $v['email'])
            ->first();

        if (!$tokenRecord || !hash_equals($tokenRecord->token, hash('sha256', (string)$request->input('token')))) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }

        if (\Carbon\Carbon::parse($tokenRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $v['email'])->delete();
            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        $user = User::where('email', $v['email'])
            ->where('student_id', $v['student_id'])
            ->first();

        if (!$user) {
            return back()->withErrors(['student_id' => 'No matching student record found for this email and student ID.']);
        }

        $user->update([
            'password' => Hash::make($v['password'])
        ]);

        DB::table('password_reset_tokens')->where('email', $v['email'])->delete();

        User::log('reset_password', $user);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! Please sign in with your new password.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            User::log('logout', $user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
