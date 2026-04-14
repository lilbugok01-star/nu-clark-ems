<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()  { return view('auth.login'); }
    public function showRegister() {
        $courses  = Course::where('is_active', true)->orderBy('code')->get();
        $sections = Section::where('is_active', true)->with('course')->orderBy('name')->get();
        return view('auth.register', compact('courses', 'sections'));
    }
    public function showForgot()   { return view('auth.forgot-password'); }

    public function login(Request $request)
    {
        $v = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($v)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return match ($user->role) {
            'admin'              => redirect()->route('admin.dashboard'),
            'organizer'          => redirect()->route('organizer.dashboard'),
            'student_department' => redirect()->route('student_department.dashboard'),
            'student'            => redirect()->route('student.dashboard'),
            default              => redirect()->route('approver.dashboard'),
        };
    }

    public function register(Request $request)
    {
        $v = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8|confirmed',
            'student_id' => ['required', 'string', 'unique:users,student_id', 'regex:/^\d{4}-\d{6}$/'],
            'course_id'  => 'required|exists:courses,id',
            'section_id' => 'required|exists:sections,id',
        ], [
            'student_id.regex' => 'The Student ID format must be YYYY-NNNNNN (e.g. 2023-190866).',
        ]);

        $user = User::create([
            ...$v,
            'password' => Hash::make($v['password']),
            'role'     => 'student',
        ]);

        AppNotification::create([
            'user_id' => $user->id,
            'type'    => 'welcome',
            'title'   => 'Welcome to NU Clark Events!',
            'message' => "Hi {$user->name}, your account has been created. Start browsing upcoming events!",
        ]);

        Auth::login($user);
        return redirect()->route('student.dashboard')->with('success', 'Welcome to NU Clark Events!');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'student_id' => 'required|string|regex:/^\d{4}-\d{6}$/',
            'password'   => 'required|min:8|confirmed',
        ], [
            'student_id.regex' => 'The Student ID format must be YYYY-NNNNNN (e.g. 2023-190866).',
        ]);

        $user = User::where('email', $request->email)
            ->where('student_id', $request->student_id)
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with that email and student ID combination.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('login')->with('success', 'Password has been reset successfully! Please sign in with your new password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
