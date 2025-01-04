<?php

namespace Tests\Unit\FriendTests;


use Tests\TestCase;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class RejectFriendRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_rejection()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a friend request
        $friendRequest = FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user2);

        // Attempt to reject the friend request
        $response = $this->postJson('/api/user/reject_friend_request', ['request_id' => $friendRequest->id]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Friend request successfully rejected.',
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
        $user3 = User::factory()->create();

        // Create a friend request
        $friendRequest = FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user3);

        // Attempt to reject the friend request
        $response = $this->postJson('/api/user/reject_friend_request', ['request_id' => $friendRequest->id]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to reject this friend request.',
                 ]);
    }

    public function test_validation_failure_returns_422()
    {
        // Simulate the authenticated user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Attempt to reject a friend request with an invalid ID
        $response = $this->postJson('/api/user/reject_friend_request', ['request_id' => 'invalid_id']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['request_id']);
    }
}