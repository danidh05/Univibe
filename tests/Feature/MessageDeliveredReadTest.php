<?php

namespace Tests\Feature;

use App\Events\MessageDelivered;
use App\Events\PrivateMessageSent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageDeliveredReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_delivered_read_receipts()
    {
        $sender = User::factory()->create(); // Create sender
        $receiver = User::factory()->create(); // Create receiver

        $this->actingAs($sender); // Simulate logged-in user

        // Data to send a message
        $data = [
            'receiver_id' => $receiver->id,
            'content' => 'Hello, this is a test message',
        ];

        // Send the request
        $response = $this->postJson('/api/messages/send', $data);

        // Assert the message was created and response is correct
        $response->assertStatus(200)
                    ->assertJson([
                    'success' => true,
                    'message' => 'Message sent successfully.',
                    ]);

        // Extract the 'data' from the JSON response
        $responseData = $response->json('data');

        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $data['content'],
            'is_read' => false,
            'is_delivered' => false,
        ]);

        // Arrange
        Event::fake(); // Fake events

        $messageData = [
            'sender_id' => $sender->id,
            'receiver_id' => $data['receiver_id'],
            'message' => $data['content'],
        ];

        // Act
        event(new PrivateMessageSent($messageData, 'test_channel'));

        // Assert
        Event::assertDispatched(PrivateMessageSent::class, function ($event) use ($messageData) {
            return $event->data === $messageData;
        });

        // now to mark the messages as delivered

        $this->actingAs($receiver);

        // Data to send a message
        $data = [
            'message_id' => $responseData['id'],
        ];

        // Send the request
        $response = $this->postJson('/api/messages/mark_message_delivered', $data);

        // Assert the message was marked as delivered and response is correct
        $response->assertStatus(200)
                    ->assertJson([
                        'success' => true,
                        'message' => 'Message marked as delivered.',
        ]);

        $responseData = $response->json('data');

        // Arrange
        Event::fake(); // Fake events

        $pusher_data = [
            'updated_message' => $responseData,
        ];

        // Act
        event(new MessageDelivered($pusher_data, 'test_channel'));

        // Assert
        Event::assertDispatched(MessageDelivered::class, function ($event) use ($pusher_data) {
            return $event->data === $pusher_data;
        });

        $this->assertDatabaseHas('messages', [
            'id' => $responseData['id'],
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $responseData['content'],
            'is_read' => false,
            'is_delivered' => true, // now true
        ]);

        // now for the read

        // Data to send a message
        $data = [
            'user_id' => $sender->id,
        ];

        // Send the request
        $response = $this->postJson('/api/messages/read_all_private_messages', $data);

        // Assert the message was marked as delivered and response is correct
        $response->assertStatus(200)
                    ->assertJson([
                        'success' => true,
                        'message' => 'Messages updated successfully.',
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $responseData['id'],
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $responseData['content'],
            'is_read' => true, // now true
            'is_delivered' => true,
        ]);
    }
}
