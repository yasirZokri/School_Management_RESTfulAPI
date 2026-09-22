<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentPayment>
 */
class StudentPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'purpose' => fake()->randomElement(['tuition', 'transport', 'books', 'uniform', 'other']),
            'paid_at' => fake()->dateTime(),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer']),
        ];
    }
}
