<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Tests\TestCase;

class SendPrivateMessagesPerformanceTest extends TestCase
{

    use RefreshDatabase;

    public function test_send_private_message_performance()
    {
        $user = User::factory()->create(); // Create sender
        $receiver = User::factory()->create(); // Create receiver
        $this->actingAs($user); // Simulate logged-in user

        // Start timer
        $startTime = microtime(true);

        $messageCount = 100; // Define the number of messages you want to send in the test

        for ($i = 0; $i < $messageCount; $i++) {
            $response = $this->postJson('/api/messages/send', [
                'receiver_id' => $receiver->id,
                'content' => 'Performance test message #' . $i,
            ]);

            $response->assertStatus(201)
                     ->assertJson([
                         'success' => true,
                         'message' => 'Message sent successfully.',
                     ]);
        }

        // Stop timer
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Log the performance time in seconds
        Log::info("Performance test for sending {$messageCount} messages took {$duration} seconds");

        // Optionally, you can set an upper limit for acceptable performance
        $this->assertLessThan(30, $duration, "Sending {$messageCount} messages took too long!"); // 30 seconds as an example threshold
    }
}
