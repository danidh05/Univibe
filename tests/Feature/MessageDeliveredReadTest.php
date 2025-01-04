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
        // Create sender and receiver
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
    
        // Simulate logged-in sender
        $this->actingAs($sender);
    
        // Data to send a message
        $data = [
            'receiver_id' => $receiver->id,
            'content' => 'Hello, this is a test message',
        ];
    
        // Send the message
        $response = $this->postJson('/api/messages/send', $data);
    
        // Assert the message was created and response is correct
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Message sent successfully.',
                 ]);
    
        // Extract the 'data' from the JSON response
        $responseData = $response->json('data');
    
        // Assert the message exists in the database
        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $data['content'],
            'is_read' => false,
            'is_delivered' => false,
        ]);
    
        // Fake events
        Event::fake();
    
        // Simulate marking the message as delivered
        $this->actingAs($receiver); // Simulate logged-in receiver
    
        // Data to mark the message as delivered
        $data = [
            'message_id' => $responseData['id'],
        ];
    
        // Send the request to mark the message as delivered
        $response = $this->postJson('/api/messages/mark_message_delivered', $data);
    
        // Assert the message was marked as delivered and response is correct
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Message marked as delivered.',
                 ]);
    
        // Extract the 'data' from the JSON response
        $responseData = $response->json('data');
    
        // Assert the message is marked as delivered in the database
        $this->assertDatabaseHas('messages', [
            'id' => $responseData['id'],
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $responseData['content'],
            'is_read' => false,
            'is_delivered' => true, // Now true
        ]);
    
        // Fake events
        Event::fake();
    
        // Data for the MessageDelivered event
        $pusher_data = [
            'updated_message' => $responseData,
        ];
    
        // Dispatch the MessageDelivered event
        event(new MessageDelivered($pusher_data, 'test_channel'));
    
        // Assert the event was dispatched
        Event::assertDispatched(MessageDelivered::class, function ($event) use ($pusher_data) {
            return $event->data === $pusher_data;
        });
    
        // Simulate marking all messages as read
        $this->actingAs($receiver); // Simulate logged-in receiver
    
        // Data to mark all messages as read
        $data = [
            'user_id' => $sender->id,
        ];
    
        // Send the request to mark all messages as read
        $response = $this->postJson('/api/messages/read_all_private_messages', $data);
    
        // Assert the messages were marked as read and response is correct
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Messages updated successfully.',
                 ]);
    
        // Assert the message is marked as read in the database
        $this->assertDatabaseHas('messages', [
            'id' => $responseData['id'],
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $responseData['content'],
            'is_read' => true, // Now true
            'is_delivered' => true,
        ]);
    }
}