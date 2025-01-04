<?php

namespace Tests\Unit\GroupMessagesTests;


use App\Models\GroupChat;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetGroupMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_group_messages_success()
    {
        $user = User::factory()->create();
        $group = GroupChat::factory()->create();
        GroupMessage::factory()->create(['sender_id' => $user->id, 'receiver_group_id' => $group->id]);

        $this->actingAs($user);

        $response = $this->getJson("/api/group/messages/get/{$group->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_get_group_messages_group_not_found()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/group/messages/get/99999');

        $response->assertStatus(404);
    }
}