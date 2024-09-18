<?php

namespace Database\Factories;

use App\Models\Major;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Major>
 */
class MajorFactory extends Factory
{
    protected $model = Major::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'major_name' => fake()->word(),
            'faculty_id' => Faculty::first() ?? Faculty::factory()->create()->id, // Ensure the faculty exists
        ];
    }
}
