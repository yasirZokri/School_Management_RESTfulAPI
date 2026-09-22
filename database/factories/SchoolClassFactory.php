<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Class '.fake()->randomLetter(),
            'grade_level' => fake()->numberBetween(1, 12),
            'section' => fake()->randomElement(['A', 'B', 'C', null]),
        ];
    }
}
