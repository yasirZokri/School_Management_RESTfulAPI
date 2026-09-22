<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a StudentPayment model into JSON, including a short summary
 * of the student who paid.
 */
class StudentPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'purpose' => $this->purpose,
            'paid_at' => $this->paid_at?->toISOString(),
            'payment_method' => $this->payment_method,
            'student' => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'student_number' => $this->student->student_number,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
