<?php

namespace App\Http\Requests\AcademicYear;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('academic_year'));
    }

    public function rules(): array
    {
        return [
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
            'name' => ['sometimes', 'string', 'max:50'],
            'starts_on' => ['sometimes', 'date'],
            'ends_on' => ['sometimes', 'date', 'after:starts_on'],
            'is_current' => ['sometimes', 'boolean'],
        ];
    }
}
