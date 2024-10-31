<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Share;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SharePostFactory extends Factory
{
    protected $model = Share::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'share_type' => $this->faker->randomElement(['user', 'feed', 'link']),
            'recipient_id' => User::factory(),
        ];
    }
}
