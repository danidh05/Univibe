<?php

namespace Tests\Feature;

use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePrivateMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_group_message_success()
    {
        $user = User::factory()->create();
        $message = GroupMessage::factory()->create(['sender_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->putJson('/api/group/messages/update', [
            'message_id' => $message->id,
            'content' => 'Updated content',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_update_group_message_unauthorized()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $message = GroupMessage::factory()->create(['sender_id' => $anotherUser->id]);

        $this->actingAs($user);

        $response = $this->putJson('/api/group/messages/update', [
            'message_id' => $message->id,
            'content' => 'Unauthorized update attempt',
        ]);

        $response->assertStatus(403);
    }
}