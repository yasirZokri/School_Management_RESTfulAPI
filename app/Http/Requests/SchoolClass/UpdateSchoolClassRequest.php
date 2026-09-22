<?php

namespace App\Http\Requests\SchoolClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
            // Same-school check as on store: only this school's years match.
            'academic_year_id' => [
                'sometimes', 'integer',
                Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $this->input('school_id'))),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'grade_level' => ['sometimes', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:50'],
        ];
    }
}
