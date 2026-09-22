<?php

namespace App\Http\Requests\Lesson;

use App\Models\TeachingAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teaching_assignment_id' => ['sometimes', 'integer', 'exists:teaching_assignments,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ];
    }

    /**
     * The same ownership check as on store, but only when the assignment
     * is actually being changed in this request.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->has('teaching_assignment_id')) {
                return;
            }

            $assignment = TeachingAssignment::find($this->input('teaching_assignment_id'));

            if ($assignment === null) {
                return;
            }

            $teacher = $this->user()->teacher;

            if ($teacher && $assignment->teacher_id !== $teacher->id) {
                $validator->errors()->add(
                    'teaching_assignment_id',
                    'Teachers can only use their own teaching assignments.'
                );
            }
        });
    }
}
