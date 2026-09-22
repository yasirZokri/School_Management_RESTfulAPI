<?php

namespace App\Http\Requests\Enrollment;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'integer', 'exists:students,id'],
            'class_id' => ['sometimes', 'integer', 'exists:classes,id'],
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
            'status' => ['sometimes', Rule::in(['active', 'completed', 'withdrawn'])],
        ];
    }

    /**
     * The same cross-school checks as on store, but only for fields that are
     * actually being changed in this request.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $class = SchoolClass::find($this->input('class_id'));
            $student = Student::find($this->input('student_id'));

            if ($class && $student && $class->school_id !== $student->school_id) {
                $validator->errors()->add('class_id', 'The class and the student must be in the same school.');
            }

            if ($class && $this->has('academic_year_id') && $class->academic_year_id !== (int) $this->input('academic_year_id')) {
                $validator->errors()->add('academic_year_id', 'The academic year does not match the class.');
            }
        });
    }
}
