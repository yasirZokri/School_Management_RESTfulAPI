<?php

namespace App\Http\Requests\TeacherSalary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => [
                'sometimes',
                'integer',
                'exists:teachers,id',
                // Same uniqueness as on store, ignoring the record being updated.
                Rule::unique('teacher_salaries')->where(fn ($query) => $query->where('month', $this->input('month')))->ignore($this->route('teacher_salary')),
            ],
            'month' => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['sometimes', 'numeric', 'decimal:0,2', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
