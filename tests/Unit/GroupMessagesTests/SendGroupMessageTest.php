<?php

namespace Tests\Unit\GroupMessagesTests;

use App\Models\GroupChat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SendGroupMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_group_message_success()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $group = GroupChat::factory()->create();
        
        $this->actingAs($user);

        $response = $this->postJson('/api/group/messages/send', [
            'group_id' => $group->id,
            'content' => 'Hello, this is a test message!',
            'media' => UploadedFile::fake()->image('test.jpg')
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_send_group_message_validation_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/group/messages/send', [
            'group_id' => null,
            'content' => '',
        ]);

        $response->assertStatus(422);
    }
}