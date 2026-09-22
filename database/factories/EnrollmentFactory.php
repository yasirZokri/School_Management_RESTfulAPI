<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        $class = SchoolClass::factory()->create();

        return [
            'student_id' => Student::factory(),
            'class_id' => $class->id,
            // The enrollment's year always matches the class's year.
            'academic_year_id' => $class->academic_year_id,
            'status' => 'active',
        ];
    }
}
