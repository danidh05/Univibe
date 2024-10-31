<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Repost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repost>
 */
class RepostFactory extends Factory
{
    protected $model = Repost::class;
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
        ];
    }
}
