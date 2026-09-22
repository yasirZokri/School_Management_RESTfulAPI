<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents one student.
 * SoftDeletes means "deleting" only fills in deleted_at; the row stays
 * in the database so payment history always points at a valid student.
 */
#[Fillable(['user_id', 'school_id', 'student_number'])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    // The account this student logs in with.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // The school this student belongs to.
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function studentPayments(): HasMany
    {
        return $this->hasMany(StudentPayment::class);
    }
}
