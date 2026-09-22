<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'employee_number' => [
                'required', 'string', 'max:50',
                // The number must be unique inside the chosen school.
                Rule::unique('teachers')->where(fn ($query) => $query->where('school_id', $this->input('school_id'))),
            ],
        ];
    }
}
