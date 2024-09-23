<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.s
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,  // Generates a random name
            'rating' => $this->faker->randomFloat(2, 0, 5),  // Rating between 0 and 5
            'university_id' => University::factory(),  // Associate instructor with a university
        ];
    }
}
