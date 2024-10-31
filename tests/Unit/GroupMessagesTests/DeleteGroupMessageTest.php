<?php

namespace Tests\Feature;

use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteGroupMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_group_message_success()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $message = GroupMessage::factory()->create(['sender_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/group/messages/delete', [
            'message_id' => $message->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_delete_group_message_unauthorized()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $message = GroupMessage::factory()->create(['sender_id' => $anotherUser->id]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/group/messages/delete', [
            'message_id' => $message->id,
        ]);

        $response->assertStatus(403);
    }
}