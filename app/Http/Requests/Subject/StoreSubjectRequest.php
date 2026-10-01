<?php

namespace App\Http\Requests\Subject;

use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Subject::class);
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            // Code must be unique inside the chosen school.
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('subjects')->where(fn ($query) => $query->where('school_id', $this->input('school_id'))),
            ],
        ];
    }
}
