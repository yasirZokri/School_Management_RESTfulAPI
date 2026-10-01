<?php

namespace App\Http\Requests\StudentPayment;

use App\Models\StudentPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('student_payment'));
    }

    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'integer', 'exists:students,id'],
            'amount' => ['sometimes', 'numeric', 'decimal:0,2', 'min:0.01'],
            'purpose' => ['sometimes', Rule::in(StudentPayment::PURPOSES)],
            'paid_at' => ['sometimes', 'date'],
            'payment_method' => ['sometimes', Rule::in(StudentPayment::PAYMENT_METHODS)],
        ];
    }
}
