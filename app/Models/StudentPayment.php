<?php

namespace App\Models;

use Database\Factories\StudentPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment made by a student (tuition, books, ...). purpose and
 * payment_method are plain strings; the allowed choices live in the
 * Form Request validation, not in the database.
 */
#[Fillable(['student_id', 'amount', 'purpose', 'paid_at', 'payment_method'])]
class StudentPayment extends Model
{
    // The only purposes and payment methods the API accepts.
    public const PURPOSES = ['tuition', 'transport', 'books', 'uniform', 'other'];

    public const PAYMENT_METHODS = ['cash', 'card', 'bank_transfer', 'other'];

    /** @use HasFactory<StudentPaymentFactory> */
    use HasFactory;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }
}
