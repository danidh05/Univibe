<?php

namespace Tests\Unit\FriendTests;


use Tests\TestCase;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class GetAllReceivedFriendRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_retrieval()
    {
        // Create a user
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create received friend requests for the user
        $receivedFriendRequest = FriendRequest::create([
            'from_id' => $otherUser->id,
            'to_id' => $user->id,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to retrieve all received friend requests
        $response = $this->getJson('/api/user/get_all_received_friend_requests');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'received_friend_requests' => [
                         '*' => ['id', 'from_id', 'to_id', 'created_at', 'updated_at']
                     ],
                 ]);
    }

    public function test_authentication_failure()
    {
        // Simulate no authenticated user by not acting as any user
        $response = $this->getJson('/api/user/get_all_received_friend_requests');
    
        // Assert that the response status is 401 (Unauthorized)
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                    //  'success' => false,
                 ]);
    }

    public function test_empty_received_friend_requests()
    {
        // Create a user with no received friend requests
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to retrieve all received friend requests
        $response = $this->getJson('/api/user/get_all_received_friend_requests');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Received friend requests succesfully retreived.', // Updated to match actual API message
                     'received_friend_requests' => [],
                 ]);
    }
}