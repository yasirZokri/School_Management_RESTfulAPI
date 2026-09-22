<?php

namespace App\Http\Requests\Enrollment;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
        ];
    }

    /**
     * Cross-school checks that need to look at the database rows:
     * - the student and the class must be in the same school;
     * - the academic year must be the class's own year.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $class = SchoolClass::find($this->input('class_id'));
            $student = Student::find($this->input('student_id'));

            if ($class && $student && $class->school_id !== $student->school_id) {
                $validator->errors()->add('class_id', 'The class and the student must be in the same school.');
            }

            if ($class && $class->academic_year_id !== (int) $this->input('academic_year_id')) {
                $validator->errors()->add('academic_year_id', 'The academic year does not match the class.');
            }
        });
    }
}
