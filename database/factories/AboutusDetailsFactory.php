<?php

namespace Database\Factories;

use App\Models\AboutusDetails;
use App\Models\Aboutus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AboutusDetailsFactory extends Factory
{
    protected $model = AboutusDetails::class;

    public function definition()
    {
        return [
            'description' => $this->faker->paragraph,
            'about_us_id' => Aboutus::factory(),
        ];
    }
}