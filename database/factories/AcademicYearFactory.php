<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->numberBetween(2026, 2030).' / '.fake()->numberBetween(1, 9).' year',
            'starts_on' => fake()->date(),
            'ends_on' => fake()->date(),
            'is_current' => false,
        ];
    }
}
