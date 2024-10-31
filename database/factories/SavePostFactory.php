<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\SavePost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavePost>
 */
class SavePostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SavePost::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(), // This creates a post for each save
            'user_id' => User::factory(), // This creates a user for each save
        ];
    }
}
