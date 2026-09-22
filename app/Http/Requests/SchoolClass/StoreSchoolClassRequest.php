<?php

namespace App\Http\Requests\SchoolClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            // The academic year must belong to the SAME school as the class.
            // This 'where' makes the exists check only look at that school's years.
            'academic_year_id' => [
                'required', 'integer',
                Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $this->input('school_id'))),
            ],
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:50'],
        ];
    }
}
