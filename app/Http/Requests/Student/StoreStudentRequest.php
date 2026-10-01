<?php

namespace App\Http\Requests\Student;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Student::class);
    }

    public function rules(): array
    {
        return [
            // These build the linked user account (created internally by the
            // controller with the Student role). No user_id is accepted here.
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'student_number' => [
                'required', 'string', 'max:50',
                // The number must be unique inside the chosen school.
                Rule::unique('students')->where(fn ($query) => $query->where('school_id', $this->input('school_id'))),
            ],
        ];
    }
}
