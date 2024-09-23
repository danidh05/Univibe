<?php

namespace Database\Factories;

use App\Models\Internship;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

class InternshipFactory extends Factory
{
    protected $model = Internship::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'major_id' => Major::factory(),  // Creates a new Major for the internship
            'description' => $this->faker->paragraph,
            'link' => $this->faker->url,
            'company' => $this->faker->company, // Add company field
        ];
    }
}
