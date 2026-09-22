<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingAssignment>
 */
class TeachingAssignmentFactory extends Factory
{
    public function definition(): array
    {
        $class = SchoolClass::factory()->create();

        return [
            'teacher_id' => Teacher::factory(),
            'class_id' => $class->id,
            'subject_id' => Subject::factory(),
            // The assignment's year always matches the class's year.
            'academic_year_id' => $class->academic_year_id,
        ];
    }
}
