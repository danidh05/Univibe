<?php

namespace Tests\Unit;


use Tests\TestCase;
use App\Models\User;
use App\Models\Follows;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;

class IsFriendTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_are_friends()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a friend relationship
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

        // Simulate user1 as authenticated user
        $this->actingAs($user1);

        // Check if user1 is friends with user2
        $response = $this->getJson('/api/user/is_friend/' . $user2->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'is_friend' => true,
                 ]);
    }

    public function test_users_are_not_friends()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate user1 as authenticated user
        $this->actingAs($user1);

        // Check if user1 is friends with user2
        $response = $this->getJson('/api/user/is_friend/' . $user2->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'is_friend' => false,
                 ]);
    }

    public function test_error_occurred()
    {
        // Mock the Auth facade to return a specific user ID
        Auth::shouldReceive('id')->andReturn(1);

        // Mock the User model
        $userMock = Mockery::mock('App\Models\User');
        $userMock->shouldReceive('find')
            ->with(1)
            ->andThrow(new ModelNotFoundException);

        // Bind the mock User to the app container
        $this->app->instance('App\Models\User', $userMock);

        // Attempt to check friendship status
        $response = $this->getJson('/api/user/is_friend/2');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'User not found.',
                 ]);
    }

    protected function tearDown(): void
    {
        // Close Mockery after each test
        Mockery::close();

        parent::tearDown();
    }
}
