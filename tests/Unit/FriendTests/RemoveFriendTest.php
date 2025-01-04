<?php

namespace Tests\Unit\FriendTests;


use Tests\TestCase;
use App\Models\User;
use App\Models\Follows;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class RemoveFriendTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_removal()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create friendship records
        Follows::create([
            'follower_id' => $user1->id,
            'followed_id' => $user2->id,
            'is_friend' => true,
        ]);

        Follows::create([
            'follower_id' => $user2->id,
            'followed_id' => $user1->id,
            'is_friend' => true,
        ]);

        // Simulate the authenticated user as the sender of the request
        $this->actingAs($user1);

        // Attempt to remove the friend
        $response = $this->deleteJson('/api/user/remove_friend', ['user_id' => $user2->id]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Friend successfully removed.',
                 ]);

        // Assert that the friendship records are deleted
        $this->assertDatabaseMissing('follows', [
            'follower_id' => $user1->id,
            'followed_id' => $user2->id,
            'is_friend' => true,
        ]);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $user2->id,
            'followed_id' => $user1->id,
            'is_friend' => true,
        ]);
    }

    public function test_self_unfriend_attempt_returns_400()
    {
        // Create a user
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to remove oneself as a friend
        $response = $this->deleteJson('/api/user/remove_friend', ['user_id' => $user->id]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You can\'t unfriend yourself.',
                 ]);
    }

    public function test_not_friends_returns_400()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user1);

        // Attempt to remove a user who is not a friend
        $response = $this->deleteJson('/api/user/remove_friend', ['user_id' => $user2->id]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You two aren\'t friends.',
                 ]);
    }

    public function test_validation_failure_returns_422()
    {
        // Simulate the authenticated user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Attempt to remove a friend with an invalid user ID
        $response = $this->deleteJson('/api/user/remove_friend', ['user_id' => 'invalid_id']);

        $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'The user_id must be a positive integer.',
        ]);
    }
}