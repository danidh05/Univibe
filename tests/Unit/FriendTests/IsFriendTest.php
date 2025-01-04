<?php

namespace Tests\Unit\FriendTests;



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
        // Disable middleware for this test (if necessary)
        $this->withoutMiddleware();
    
        // Create a user and authenticate them
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    
        // Mock the User model to throw a ModelNotFoundException for the target user
        $userMock = Mockery::mock('App\Models\User');
        $userMock->shouldReceive('findOrFail')
            ->with($user->id) // The authenticated user ID
            ->andReturn($user); // Return the authenticated user
    
        $userMock->shouldReceive('findOrFail')
            ->with(999999) // The non-existent target user ID
            ->andThrow(new ModelNotFoundException); // Throw ModelNotFoundException
    
        // Mock the isFriend method to return false (since the target user does not exist)
        $userMock->shouldReceive('isFriend')
            ->with(999999) // The non-existent target user ID
            ->andReturn(false); // Return false
    
        // Bind the mock User to the app container
        $this->app->instance('App\Models\User', $userMock);
    
        // Attempt to check friendship status for a non-existent user
        $response = $this->getJson('/api/user/is_friend/999999'); // Non-existent user ID
    
        // Assert the response
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