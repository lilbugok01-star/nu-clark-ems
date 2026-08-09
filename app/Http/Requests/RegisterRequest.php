<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'email'      => [
                'required',
                'email',
                'unique:users',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@students.nu-clark.edu.ph')) {
                        $fail('Only official NU Clark student emails (@students.nu-clark.edu.ph) are allowed.');
                    }
                },
            ],
            'password'   => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'student_id' => ['required', 'string', 'unique:users,student_id', 'regex:/^\d{4}-\d{6}$/'],
            'course_id'  => 'required|exists:courses,id',
            'section_id' => 'required|exists:sections,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.regex' => 'The Student ID format must be YYYY-NNNNNN (e.g. 2023-190866).',
        ];
    }
}
