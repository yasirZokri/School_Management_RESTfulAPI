<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherSalary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherSalary>
 */
class TeacherSalaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'amount' => fake()->randomFloat(2, 1000, 8000),
            'month' => '2026-09', // kept fixed so factory-made rows never clash
            'paid_at' => null,
        ];
    }
}
