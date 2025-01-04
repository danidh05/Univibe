<?php

namespace Tests\Unit\FriendTests;


use Tests\TestCase;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class GetAllSentFriendRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_retrieval()
    {
        // Create a user
        $user = User::factory()->create();

        // Create sent friend requests for the user
        $sentFriendRequest = FriendRequest::create([
            'from_id' => $user->id,
            'to_id' => User::factory()->create()->id,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to retrieve all sent friend requests
        $response = $this->getJson('/api/user/get_all_sent_friend_requests');

        $response->assertStatus(200)
                ->assertJsonStructure([
                     'sent_friend_requests' => [
                        '*' => ['id', 'from_id', 'to_id', 'created_at', 'updated_at'] 
                     ],
                 ]);
    }

    public function test_authentication_failure()
    {
        // Simulate no authenticated user by making the request without authentication
        $response = $this->getJson('/api/user/get_all_sent_friend_requests');
    
        // Assert that the response is 401 Unauthorized
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    public function test_empty_sent_friend_requests()
    {
        // Create a user with no sent friend requests
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to retrieve all sent friend requests
        $response = $this->getJson('/api/user/get_all_sent_friend_requests');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Sent friend requests successfully retrieved.',
                     'sent_friend_requests' => [],
                 ]);
    }
}