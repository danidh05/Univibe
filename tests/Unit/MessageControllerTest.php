<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test cases go here

    public function test_send_private_message()
    {
        // Create a role first
        $role = Role::factory()->create(['role_name' => 'User']);
        
        // Create a user and assign the created role
        $sender = User::factory()->create(['role_id' => $role->id]);
        $receiver = User::factory()->create(['role_id' => $role->id]);

        // Mock authentication
        $this->actingAs($sender);

        // Data to send a message
        $data = [
            'receiver_id' => $receiver->id,
            'content' => 'Hello, this is a test message',
        ];

        // Send the request
        $response = $this->postJson('/api/messages/send', $data);

        // Assert the message was created and response is correct
        $response->assertStatus(201)
                 ->assertJson([
                    'success' => true,
                    'message' => 'Message sent successfully.',
                 ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $data['content'],
        ]);
    }

    public function test_send_private_message_validation_error()
    {
        // Send request without receiver_id and content
        $response = $this->postJson('/api/messages/send', []);

        $response->assertStatus(422) // Validation error
                 ->assertJsonValidationErrors(['receiver_id', 'content']);
    }

    public function test_get_private_messages()
    {
        // Create a role first
        $role = Role::factory()->create(['role_name' => 'User']);
        
        // Create a user and assign the created role
        $authUser = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);

        // Mock authentication
        $this->actingAs($authUser);

        // Create messages between users
        Message::factory()->create([
            'sender_id' => $authUser->id,
            'receiver_id' => $otherUser->id,
            'content' => 'Message from auth user to other user',
        ]);

        Message::factory()->create([
            'sender_id' => $otherUser->id,
            'receiver_id' => $authUser->id,
            'content' => 'Message from other user to auth user',
        ]);

        // Send request to get messages
        $response = $this->getJson('/api/messages/get?user_id=' . $otherUser->id);

        // Assert correct response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => ['id', 'sender_id', 'receiver_id', 'content', 'created_at'],
                    ],
                ]);
    }

    public function test_get_private_messages_validation_error()
    {
        $response = $this->getJson('/api/messages/get', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    public function test_delete_private_message()
    {
        
        // Create a role first
        $role = Role::factory()->create(['role_name' => 'User']);

        // Create a user and assign the created role
        $authUser = User::factory()->create(['role_id' => $role->id]);

        // Mock authentication
        $this->actingAs($authUser);

        // Create a message by the auth user
        $message = Message::factory()->create([
            'sender_id' => $authUser->id,
        ]);

        // Send delete request
        $response = $this->deleteJson('/api/messages/delete', ['message_id' => $message->id]);

        // Assert response
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Message deleted successfully.',
                ]);

        // Ensure the message was deleted
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_delete_private_message_unauthorized()
    {
        // Create a role first
        $role = Role::factory()->create(['role_name' => 'User']);

        // Create a user and assign the created role
        $authUser = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);

        // Mock authentication
        $this->actingAs($authUser);

        // Create a message by another user
        $message = Message::factory()->create([
            'sender_id' => $otherUser->id,
        ]);

        // Send delete request
        $response = $this->deleteJson('/api/messages/delete', ['message_id' => $message->id]);

        // Assert unauthorized response
        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ]);
    }

    public function test_update_private_message()
    {
        // Create a role first
        $role = Role::factory()->create(['role_name' => 'User']);

        // Create a user and assign the created role
        $authUser = User::factory()->create(['role_id' => $role->id]);

        // Mock authentication
        $this->actingAs($authUser);

        // Create a message
        $message = Message::factory()->create([
            'sender_id' => $authUser->id,
            'content' => 'Old content',
        ]);

        // Send update request
        $newContent = 'Updated content';
        $response = $this->putJson('/api/messages/update', [
            'message_id' => $message->id,
            'content' => $newContent,
        ]);

        // Assert response
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Message updated successfully.',
                ]);

        // Ensure the message content was updated
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => $newContent,
        ]);
    }

    public function test_update_private_message_validation_error()
    {
        $response = $this->putJson('/api/messages/update', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['message_id', 'content']);
    }
}
