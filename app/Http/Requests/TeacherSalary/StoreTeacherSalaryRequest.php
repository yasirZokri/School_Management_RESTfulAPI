<?php

namespace App\Http\Requests\TeacherSalary;

use App\Models\TeacherSalary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TeacherSalary::class);
    }

    public function rules(): array
    {
        return [
            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
                // A teacher cannot have two salary rows for the same month.
                Rule::unique('teacher_salaries')->where(fn ($query) => $query->where('month', $this->input('month'))),
            ],
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
