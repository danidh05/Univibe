<?php

namespace Database\Factories;

use App\Models\GroupChat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupChat>
 */
class GroupChatFactory extends Factory
{

    protected $model = GroupChat::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_name' => $this->faker->word, // Example name, adjust as needed
            'owner_id' => User::factory(), // Assuming the owner is a User model
        ];
    }

    /**
     * Configure the factory to add the pusher_channel after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (GroupChat $groupChat) {
            // Set the pusher_channel based on the user's ID
            $groupChat->group_pusher_channel = 'group-' . $groupChat->id;
            $groupChat->save();
        });
    }
}
