<?php

namespace Database\Factories;

use App\Models\Aboutus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AboutusFactory extends Factory
{
    protected $model = Aboutus::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
        ];
    }
}