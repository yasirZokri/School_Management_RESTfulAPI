<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
        ];
    }
}
