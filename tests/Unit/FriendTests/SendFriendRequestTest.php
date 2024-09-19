<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Follows;
use Mockery\MockInterface;
use App\Models\FriendRequest;
use App\Services\NotificationService;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendFriendRequestTest extends TestCase
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

    public function test_user_not_found_returns_404()
    {
        // Create authenticated user
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);
        $response = $this->postJson('/api/user/send_friend_request', ['user_id' => 999]);

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'The user with the provided user_id does not exist.',
                 ]);
    }

    public function test_cannot_send_friend_request_to_yourself()
    {
        // Create authenticated user
        $user = User::factory()->create();

        // Simulate the authenticated user
        $this->actingAs($user);

        $response = $this->postJson('/api/user/send_friend_request', ['user_id' => $user->id]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You cannot send a friend request to yourself.',
                 ]);
    }

    public function test_cannot_send_friend_request_if_already_sent()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate a friend request already sent
        FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user1);

        $response = $this->postJson('/api/user/send_friend_request', ['user_id' => $user2->id]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You have already sent a friend request to this user.',
                 ]);
    }

    public function test_cannot_send_friend_request_if_already_received_one_from_user()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate a friend request already sent
        FriendRequest::create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user2);

        $response = $this->postJson('/api/user/send_friend_request', ['user_id' => $user1->id]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You have a pending friend request from this user.',
                 ]);
    }

    public function test_cannot_send_friend_request_if_already_friends()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate that they are already friends
        Follows::create([
            'follower_id' => $user1->id,
            'followed_id' => $user2->id,
            'is_friend' => true,
        ]);

        // Simulate the authenticated user
        $this->actingAs($user1);

        $response = $this->postJson('/api/user/send_friend_request', ['user_id' => $user2->id]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are already friends with this user.',
                 ]);
    }

    public function test_send_friend_request_successfully()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Mock notification creation
        $this->notificationService->expects($this->once())
            ->method('createNotification')
            ->with(
                $this->equalTo($user2->id),
                $this->equalTo('friend_request'),
                $this->stringContains('sent you a friend request'),
                $this->isType('object'),
                $this->equalTo($user2->pusher_channel)
            );

        // Simulate the authenticated user
        $this->actingAs($user1);

        $response = $this->postJson('/api/user/send_friend_request', ['user_id' => $user2->id]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Friend request succesfully sent.',
                 ]);

        $this->assertDatabaseHas('friend_requests', [
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);
    }
}
