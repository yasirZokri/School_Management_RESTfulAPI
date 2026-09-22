<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Create a fresh login account for the student automatically.
            'user_id' => User::factory()->role(UserRole::Student),
            'school_id' => School::factory(),
            'student_number' => 'STU'.fake()->unique()->numberBetween(1000, 9999),
        ];
    }
}
