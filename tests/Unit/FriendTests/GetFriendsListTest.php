<?php

namespace Tests\Unit\FriendTests;



use Tests\TestCase;
use App\Models\User;
use App\Models\Follows;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetFriendsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_retrieval()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a friend for the user
        $friend = User::factory()->create();

        // Create friendship records
        Follows::create([
            'follower_id' => $user->id,
            'followed_id' => $friend->id,
            'is_friend' => true,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to retrieve the friends list
        $response = $this->getJson('/api/user/get_friends_list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'friends_list' => [
                    '*' => ['id', 'username', 'email', 'profile_picture', 'is_active'] // Adjust this based on the actual structure of User
                ],
            ]);
    }

    public function test_failure_due_to_unauthenticated_request()
    {
        // Simulate an unauthenticated request (no user logged in)
        Auth::logout(); // Log out the user
    
        // Attempt to retrieve the friends list
        $response = $this->getJson('/api/user/get_friends_list');
    
        // Assert the response
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ])
                 ->assertJsonMissing([
                     'success' => false,
                 ]);
    }

    public function test_empty_friend_list()
    {
        // Create a user with no friends
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to retrieve the friends list
        $response = $this->getJson('/api/user/get_friends_list');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Friend list successfully retrieved.',
                     'friends_list' => [],
                 ]);
    }
}