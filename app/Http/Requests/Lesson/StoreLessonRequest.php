<?php

namespace App\Http\Requests\Lesson;

use App\Models\TeachingAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teaching_assignment_id' => ['required', 'integer', 'exists:teaching_assignments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ];
    }

    /**
     * Ownership check: a teacher may only create lessons for their own
     * teaching assignments. Admins (who have no teacher profile) skip it.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $assignment = TeachingAssignment::find($this->input('teaching_assignment_id'));

            if ($assignment === null) {
                return;
            }

            $teacher = $this->user()->teacher;

            if ($teacher && $assignment->teacher_id !== $teacher->id) {
                $validator->errors()->add(
                    'teaching_assignment_id',
                    'Teachers can only create lessons for their own teaching assignments.'
                );
            }
        });
    }
}
