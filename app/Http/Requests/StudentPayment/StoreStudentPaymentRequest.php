<?php

namespace App\Http\Requests\StudentPayment;

use App\Models\StudentPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
            'purpose' => ['required', Rule::in(StudentPayment::PURPOSES)],
            'paid_at' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(StudentPayment::PAYMENT_METHODS)],
        ];
    }
}
