<?php

namespace Tests\Unit;


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

    public function test_failure_due_to_exception()
    {
        // Attempt to retrieve the friends list
        $response = $this->getJson('/api/user/get_friends_list');

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => "No query results for model [App\Models\User]."
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
