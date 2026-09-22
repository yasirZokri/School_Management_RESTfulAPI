<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents one teacher.
 * SoftDeletes is used here too, so salary records always point at a
 * valid teacher row even after the teacher has "left" the school.
 */
#[Fillable(['user_id', 'school_id', 'employee_number'])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory, SoftDeletes;

    // The account this teacher logs in with.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // The school this teacher works at.
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function teacherSalaries(): HasMany
    {
        return $this->hasMany(TeacherSalary::class);
    }

    // Every lesson this teacher teaches, going through the assignments.
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, TeachingAssignment::class);
    }
}
