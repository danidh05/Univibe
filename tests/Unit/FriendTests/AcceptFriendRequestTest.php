<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\FriendRequest;
use App\Models\Follows;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AcceptFriendRequestTest extends TestCase
{
    use RefreshDatabase;

    private $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock the NotificationService
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->app->instance(NotificationService::class, $this->notificationService);
    }

    public function test_successful_friend_request_acceptance()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a friend request
        $friendRequest = FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Mock notification creation
        $this->notificationService->expects($this->once())
            ->method('createNotification')
            ->with(
                $this->equalTo($user1->id),
                $this->equalTo('friend_accepted'),
                $this->stringContains('accepted your friend request'),
                $this->isNull(),
                $this->equalTo($user1->pusher_channel)
            );

        // Simulate the authenticated user
        $this->actingAs($user2);

        // Accept the friend request
        $response = $this->postJson('/api/user/accept_friend_request', ['request_id' => $friendRequest->id]);

        // Assert successful response
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Friend request succesfully accepted.',
                 ]);

        // Assert that the friend request was deleted
        $this->assertDatabaseMissing('friend_requests', [
            'id' => $friendRequest->id,
        ]);

        // Assert that friendships were created or updated
        $this->assertDatabaseHas('follows', [
            'follower_id' => $user1->id,
            'followed_id' => $user2->id,
            'is_friend' => true,
        ]);
        $this->assertDatabaseHas('follows', [
            'follower_id' => $user2->id,
            'followed_id' => $user1->id,
            'is_friend' => true,
        ]);
    }

    public function test_friend_request_not_found_returns_500()
    {
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);

        // Attempt to accept a non-existent friend request
        $response = $this->postJson('/api/user/accept_friend_request', ['request_id' => 999]);

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => "The selected request id is invalid.",
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

        // Attempt to accept the friend request
        $response = $this->postJson('/api/user/accept_friend_request', ['request_id' => $friendRequest->id]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to accept this friend request.',
                 ]);
    }
}