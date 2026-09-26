<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
            'employee_number' => [
                'sometimes', 'string', 'max:50',
                // Unique per school, but ignore the teacher being edited.
                Rule::unique('teachers')
                    ->where(fn ($query) => $query->where('school_id', $this->input('school_id')))
                    ->ignore($this->route('teacher')),
            ],
        ];
    }
}
