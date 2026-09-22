<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            // Code must be unique inside the school, ignoring this subject.
            'code' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('subjects')
                    ->where(fn ($query) => $query->where('school_id', $this->input('school_id')))
                    ->ignore($this->route('subject')),
            ],
        ];
    }
}
