<?php

namespace Database\Factories;

use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,  // Random course title
            'description' => $this->faker->paragraph,  // Random course description
            'instructor_id' => Instructor::factory(),  // Associate course with an instructor
            'isFree' => $this->faker->boolean,  // Random true/false
            'price' => $this->faker->randomFloat(2, 10, 500),  // Price between $10 and $500
        ];
    }
}
