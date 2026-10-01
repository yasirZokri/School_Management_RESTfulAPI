<?php

namespace App\Http\Requests\TeachingAssignment;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTeachingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('teaching_assignment'));
    }

    public function rules(): array
    {
        return [
            'teacher_id' => [
                'sometimes', 'integer', 'exists:teachers,id',
                Rule::unique('teaching_assignments')
                    ->where(fn ($query) => $query
                        ->where('class_id', $this->input('class_id'))
                        ->where('subject_id', $this->input('subject_id'))
                        ->where('academic_year_id', $this->input('academic_year_id')))
                    ->ignore($this->route('teaching_assignment')),
            ],
            'class_id' => ['sometimes', 'integer', 'exists:classes,id'],
            'subject_id' => ['sometimes', 'integer', 'exists:subjects,id'],
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
        ];
    }

    /**
     * Same cross-school checks as on store.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $class = SchoolClass::find($this->input('class_id'));
            $teacher = Teacher::find($this->input('teacher_id'));
            $subject = Subject::find($this->input('subject_id'));

            if ($class && $teacher && $class->school_id !== $teacher->school_id) {
                $validator->errors()->add('teacher_id', 'The teacher and the class must be in the same school.');
            }

            if ($class && $subject && $class->school_id !== $subject->school_id) {
                $validator->errors()->add('subject_id', 'The subject and the class must be in the same school.');
            }

            if ($class && $this->has('academic_year_id') && $class->academic_year_id !== (int) $this->input('academic_year_id')) {
                $validator->errors()->add('academic_year_id', 'The academic year does not match the class.');
            }
        });
    }
}
