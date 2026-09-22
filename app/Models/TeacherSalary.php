<?php

namespace App\Models;

use Database\Factories\TeacherSalaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A month's salary for one teacher. The unique index (teacher_id, month)
 * stops two salaries for the same month. month looks like "2026-09".
 */
#[Fillable(['teacher_id', 'amount', 'month', 'paid_at'])]
class TeacherSalary extends Model
{
    /** @use HasFactory<TeacherSalaryFactory> */
    use HasFactory;

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', // always show two decimals, e.g. "3000.00"
            'paid_at' => 'datetime',
        ];
    }
}
