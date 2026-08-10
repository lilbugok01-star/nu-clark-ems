<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || Auth::user()->role !== 'admin') {
                    abort(403, 'Admin access only.');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * Show the student CSV import page
     */
    public function showImport()
    {
        $courses  = Course::where('is_active', true)->get();
        $sections = Section::where('is_active', true)->with('course')->get();
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
        ];
        return view('admin.import-students', compact('courses', 'sections', 'stats'));
    }

    /**
     * Download the CSV template for student import
     */
    public function downloadTemplate()
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="nu_clark_students_template.csv"'];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            // Header row
            fputcsv($handle, ['first_name', 'middle_name', 'surname', 'email', 'student_id', 'course_code', 'section_name', 'password']);
            // Example rows
            fputcsv($handle, ['Juan',  'dela',   'Cruz',   'juan.delacruz@students.nu-clark.edu.ph',  '2022-00001', 'BSIT', 'BSIT-3A', 'Password123!']);
            fputcsv($handle, ['Maria', '',       'Santos', 'maria.santos@students.nu-clark.edu.ph',    '2022-00002', 'BSCS', 'BSCS-2B', 'Password123!']);
            fputcsv($handle, ['Pedro', 'Garcia', 'Reyes',  'pedro.reyes@students.nu-clark.edu.ph',     '2022-00003', 'BSBA', 'BSBA-1A', 'Password123!']);
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    // previewCsv route has been removed.

    /**
     * Actually run the import
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'csv_file'         => 'required|file|mimes:csv,txt|max:5120',
            'default_password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'default_role'     => 'required|in:student',
            'skip_errors'      => 'nullable|boolean',
        ]);

        $path           = $request->file('csv_file')->getRealPath();
        $defaultPass    = Hash::make($request->default_password);
        $skipErrors     = $request->boolean('skip_errors', true);
        $imported       = 0;
        $skipped        = 0;
        $errorList      = [];
        $rowNum         = 0;

        if (($handle = fopen($path, 'r')) !== false) {
            fgetcsv($handle); // skip header
            while (($line = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($line) < 5) { $skipped++; continue; }

                [$firstNameOrName, $col2, $col3, $col4, $col5, $col6, $col7, $col8] = array_pad(array_map('trim', $line), 8, '');

                // Detect CSV format: new format has 8 columns (first_name, middle_name, surname, email, student_id, course_code, section_name, password)
                // Legacy format has 6 columns (name, email, student_id, course_code, section_name, password)
                if (count($line) >= 7) {
                    // New format: first_name, middle_name, surname, email, student_id, course_code, section_name, password
                    $firstName   = $firstNameOrName;
                    $middleName  = $col2 ?: null;
                    $surname     = $col3;
                    $email       = $col4;
                    $studentId   = $col5;
                    $courseCode  = $col6;
                    $sectionName = $col7;
                    $plainPass   = $col8;
                } else {
                    // Legacy format: name, email, student_id, course_code, section_name, password
                    // Auto-split name into parts
                    $nameParts = preg_split('/\s+/', $firstNameOrName);
                    $firstName  = $nameParts[0] ?? '';
                    $surname    = count($nameParts) > 1 ? array_pop($nameParts) : '';
                    array_shift($nameParts);
                    $middleName = count($nameParts) > 0 ? implode(' ', $nameParts) : null;
                    $email       = $col2;
                    $studentId   = $col3;
                    $courseCode  = $col4;
                    $sectionName = $col5;
                    $plainPass   = $col6;
                }

                // Validation
                if (!$firstName || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    $errorList[] = "Row {$rowNum}: invalid name or email — skipped.";
                    continue;
                }
                if (User::where('email', $email)->exists()) {
                    $skipped++;
                    $errorList[] = "Row {$rowNum}: {$email} already exists — skipped.";
                    continue;
                }
                if ($studentId && User::where('student_id', $studentId)->exists()) {
                    $skipped++;
                    $errorList[] = "Row {$rowNum}: student ID {$studentId} already exists — skipped.";
                    continue;
                }

                $course  = Course::where('code', $courseCode)->first();
                $section = Section::where('name', $sectionName)->first();

                User::create([
                    'first_name'  => $firstName,
                    'middle_name' => $middleName,
                    'surname'     => $surname,
                    'email'      => $email,
                    'student_id' => $studentId ?: null,
                    'course_id'  => $course?->id,
                    'section_id' => $section?->id,
                    'role'       => 'student',
                    'password'   => $plainPass ? Hash::make($plainPass) : $defaultPass,
                    'is_active'  => true,
                ]);
                $imported++;
            }
            fclose($handle);
        }

        $msg = "✅ Imported {$imported} students successfully.";
        if ($skipped) $msg .= " ⚠️ {$skipped} rows skipped (duplicates or errors).";

        return redirect()->route('admin.import')
            ->with('success', $msg)
            ->with('import_errors', $errorList);
    }
}
