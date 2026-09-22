<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Create a fresh login account for the teacher automatically.
            'user_id' => User::factory()->role(UserRole::Teacher),
            'school_id' => School::factory(),
            'employee_number' => 'EMP'.fake()->unique()->numberBetween(1000, 9999),
        ];
    }
}
