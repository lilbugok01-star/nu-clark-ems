<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('course', 'section');

        if ($request->role)   $query->where('role', $request->role);
        if ($request->search) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname'     => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'role'       => 'required|in:admin,organizer,student',
            'student_id' => 'nullable|string|unique:users,student_id|regex:/^\d{4}-\d{6}$/',
            'course_id'  => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
        ], [
            'student_id.regex' => 'The Student ID format must be YYYY-NNNNNN (e.g. 2023-190866).',
        ]);

        $user = User::create([...$validated, 'password' => Hash::make($validated['password'])]);

        return response()->json(['status' => 'success', 'user' => $user->load('course', 'section')], 201);
    }

    public function show($id)
    {
        $user = User::with('course', 'section', 'registrations.event')->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name'  => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname'     => 'sometimes|string|max:255',
            'email'      => "sometimes|email|unique:users,email,{$id}",
            'role'       => 'sometimes|in:admin,organizer,student',
            'student_id' => ["sometimes", "nullable", "regex:/^\d{4}-\d{6}$/", "unique:users,student_id,{$id}"],
            'course_id'  => 'sometimes|nullable|exists:courses,id',
            'section_id' => 'sometimes|nullable|exists:sections,id',
            'password'   => 'sometimes|string|min:8|confirmed',
            'is_active'  => 'sometimes|boolean',
        ], [
            'student_id.regex' => 'The Student ID format must be YYYY-NNNNNN (e.g. 2023-190866).',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json(['status' => 'success', 'user' => $user->load('course', 'section')]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    // Course & Section endpoints
    public function courses()
    {
        return response()->json(Course::with('sections')->where('is_active', true)->get());
    }

    public function sections(Request $request)
    {
        $query = Section::with('course')->where('is_active', true);
        if ($request->course_id) $query->where('course_id', $request->course_id);
        return response()->json($query->get());
    }
}
