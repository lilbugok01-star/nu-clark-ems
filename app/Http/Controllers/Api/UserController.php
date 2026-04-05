<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('course', 'section');

        if ($request->role)   $query->where('role', $request->role);
        if ($request->search) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8',
            'role'       => 'required|in:admin,organizer,student',
            'student_id' => 'nullable|string|unique:users,student_id',
            'course_id'  => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
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
            'name'       => 'sometimes|string|max:255',
            'email'      => "sometimes|email|unique:users,email,{$id}",
            'role'       => 'sometimes|in:admin,organizer,student',
            'student_id' => "sometimes|nullable|unique:users,student_id,{$id}",
            'course_id'  => 'sometimes|nullable|exists:courses,id',
            'section_id' => 'sometimes|nullable|exists:sections,id',
            'password'   => 'sometimes|string|min:8|confirmed',
            'is_active'  => 'sometimes|boolean',
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
