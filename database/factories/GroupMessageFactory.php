<?php

namespace Database\Factories;

use App\Models\GroupChat;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupMessage>
 */
class GroupMessageFactory extends Factory
{
    protected $model = GroupMessage::class;

    public function definition()
    {
        return [
            'sender_id' => User::factory(),
            'receiver_group_id' => GroupChat::factory(),
            'content' => $this->faker->sentence(),
            'media_url' => null, // Optional, can set a fake URL if media is needed
            'message_type' => 'text', // Could also randomize or switch based on scenario
        ];
    }
}
