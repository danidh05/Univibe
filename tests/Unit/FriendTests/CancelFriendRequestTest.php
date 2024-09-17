<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class CancelFriendRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_cancellation()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a friend request
        $friendRequest = FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Simulate the authenticated user as the request sender
        $this->actingAs($user1);

        // Attempt to cancel the friend request
        $response = $this->postJson('/api/user/cancel_friend_request', ['request_id' => $friendRequest->id]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Friend request successfully canceled.',
                 ]);

        // Assert that the friend request is deleted
        $this->assertDatabaseMissing('friend_requests', [
            'id' => $friendRequest->id,
        ]);
    }

    public function test_authorization_failure_returns_403()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a friend request
        $friendRequest = FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Simulate the authenticated user who is not the request sender
        $this->actingAs($user2);

        // Attempt to cancel the friend request
        $response = $this->postJson('/api/user/cancel_friend_request', ['request_id' => $friendRequest->id]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to cancel this friend request.',
                 ]);
    }

    public function test_validation_failure_returns_422()
    {
        // Simulate the authenticated user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Attempt to cancel a friend request with an invalid ID
        $response = $this->postJson('/api/user/cancel_friend_request', ['request_id' => 'invalid_id']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['request_id']);
    }
}
