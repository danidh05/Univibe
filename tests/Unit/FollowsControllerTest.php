<?php

namespace Tests\Unit;

use App\Models\Follows;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FollowsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_follows_a_user_successfully()
    {
        // Create users
        $follower = User::factory()->create();
        $followed = User::factory()->create();
    
        // Act as the follower
        $this->actingAs($follower);
    
        // Perform the follow request
        $response = $this->postJson('/api/user/follow', [
            'user_id' => $followed->id,
        ]);
    
        // Assert the follow was created
        $response->assertStatus(200)
                 ->assertJson(function (AssertableJson $json) use ($follower, $followed) {
                     $json->where('success', true)
                          ->where('message', 'User succesfully followed.')
                          ->has('follow')
                          ->where('follow.follower_id', $follower->id)
                          ->where('follow.followed_id', $followed->id)
                          ->where('follow.is_friend', false);
                 });
    
        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'is_friend' => false,
        ]);
    }

    public function test_follows_returns_an_error_when_no_user_id_is_provided()
    {
        // Create a user to act as the follower
        $follower = User::factory()->create();

        // Act as the follower
        $this->actingAs($follower);

        // Perform the follow request without user_id
        $response = $this->postJson('/api/user/follow', []);

        // Assert the response status and JSON structure
        $response->assertStatus(422) // Assuming you are using validation
                 ->assertJson([
                     'success' => false,
                     'message' => 'The user_id parameter is required.', // Adjust the message as needed
                 ]);
    }

    public function test_follows_returns_an_error_when_non_existing_user_id_is_provided()
    {
        // Create a user to act as the follower
        $follower = User::factory()->create();

        // Act as the follower
        $this->actingAs($follower);

        // Perform the follow request without user_id
        $response = $this->postJson('/api/user/follow', [
            'user_id' => 9999,
        ]);

        // Assert the response status and JSON structure
        $response->assertStatus(404) // Assuming you are using validation
                 ->assertJson([
                     'success' => false,
                     'message' => 'The user with the provided user_id does not exist.', // Adjust the message as needed
                 ]);
    }

    public function test_it_unfollows_a_user_successfully()
    {
        // Create users
        $follower = User::factory()->create();
        $followed = User::factory()->create();

        // Create a follow relationship
        Follows::create([
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'is_friend' => false,
        ]);

        // Act as the follower
        $this->actingAs($follower);

        // Perform the unfollow request
        $response = $this->postJson('/api/user/unfollow', [
            'user_id' => $followed->id,
        ]);

        // Assert the follow was deleted
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User succesfully unfollowed.',
                 ]);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);
    }

    public function test_unfollows_returns_an_error_when_no_user_id_is_provided()
    {
        $follower = User::factory()->create();
        $followed = User::factory()->create();
    
        // Act as the follower
        $this->actingAs($follower);
    
        // Perform the unfollow request
        $response = $this->postJson('/api/user/unfollow', []);
    
        // Assert the response status and JSON structure
        $response->assertStatus(422) // Assuming you are using validation
                 ->assertJson([
                     'success' => false,
                     'message' => 'The user_id parameter is required.', // Adjust the message as needed
                 ]);
    }

    public function test_unfollows_returns_an_error_when_non_existing_user_id_is_provided()
    {
        // Create a user to act as the follower
        $follower = User::factory()->create();

        // Act as the follower
        $this->actingAs($follower);

        // Perform the follow request without user_id
        $response = $this->postJson('/api/user/unfollow', [
            'user_id' => 10,
        ]);

        // Assert the response status and JSON structure
        $response->assertStatus(404) // Assuming you are using validation
                 ->assertJson([
                     'success' => false,
                     'message' => 'The user with the provided user_id does not exist.', // Adjust the message as needed
                 ]);
    }

    public function test_unfollows_a_user_you_dont_follow()
    {
        $follower = User::factory()->create();
        $followed = User::factory()->create();
    
        // Act as the follower
        $this->actingAs($follower);
    
        // Perform the unfollow request
        $response = $this->postJson('/api/user/unfollow', [
            'user_id' => $followed->id,
        ]);
    
        // Assert that the response status and JSON message are correct
        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not following this user.',
                 ]);
    }

    public function test_is_following_successfully_checks_if_user_is_following()
    {
        // Create users
        $follower = User::factory()->create();
        $followed = User::factory()->create();

        // Create a follow relationship
        Follows::create([
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'is_friend' => false,
        ]);

        // Act as the follower
        $this->actingAs($follower);

        // Perform the is_following request
        $response = $this->getJson("/api/user/is_following/{$followed->id}");

        // Assert the response status and JSON structure
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'You are following this user.',
                     'is_following' => true,
                 ]);
    }

    public function test_is_following_returns_an_error_when_no_user_id_is_provided()
    {
        // Act as a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Perform the is_following request without user_id
        $response = $this->getJson('/api/is_following');

        // Assert the response status and JSON structure
        $response->assertStatus(404);
    }

    public function test_is_following_returns_an_error_when_user_not_found()
    {
        // Act as a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Perform the is_following request with a non-existent user_id
        $response = $this->getJson("/api/user/is_following/9999");

        // Assert the response status and JSON structure
        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'User not found.',
                 ]);
    }

    public function test_is_following_returns_not_following_when_no_relationship_exists()
    {
        // Create users without a follow relationship
        $follower = User::factory()->create(); // This is the authenticated user
        $followed = User::factory()->create(); // The user that might be followed

        // Act as the follower
        $this->actingAs($follower);

        // Perform the is_following request
        $response = $this->getJson("/api/user/is_following/{$followed->id}");

        // Assert the response status and JSON structure
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'You are not following this user.',
                    'is_following' => false,
                ]);
    }

    public function test_is_followed_successfully_checks_if_user_is_followed()
    {
        // Create users
        $follower = User::factory()->create(); // This user follows the authenticated user
        $followed = User::factory()->create(); // This is the authenticated user

        // Create a follow relationship
        Follows::create([
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'is_friend' => false,
        ]);

        // Act as the followed user
        $this->actingAs($followed);

        // Perform the is_followed request using the correct route
        $response = $this->getJson("/api/user/is_followed/{$follower->id}");

        // Assert the response status and JSON structure
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'You are followed by this user.',
                     'is_following' => true,
                 ]);
    }

    public function test_is_followed_returns_an_error_when_no_user_id_is_provided()
    {
        // Act as a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Perform the is_followed request with an invalid URL (no user_id)
        $response = $this->getJson('/api/user/is_followed/'); // This should simulate missing the user_id

        // Assert the response status (404 or 422) based on how the route handles missing parameters
        $response->assertStatus(404); // Adjust this status code based on actual behavior
    }

    public function test_is_followed_returns_an_error_when_user_not_found()
    {
        // Act as a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Perform the is_followed request with a non-existent user_id
        $response = $this->getJson('/api/user/is_followed/9999'); // Assuming user_id 9999 doesn't exist

        // Assert the response status and JSON structure
        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'User not found.',
                 ]);
    }

    public function test_is_followed_returns_not_followed_when_no_relationship_exists()
    {
        // Create users without a follow relationship
        $follower = User::factory()->create(); // This user is supposed to follow
        $followed = User::factory()->create(); // This is the authenticated user

        // Act as the followed user
        $this->actingAs($followed);

        // Perform the is_followed request
        $response = $this->getJson("/api/user/is_followed/{$follower->id}");

        // Assert the response status and JSON structure
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'You are not followed by this user.',
                     'is_following' => false,
                 ]);
    }

    public function test_get_follower_list_successfully_retrieves_follower_list()
    {
        // Create a user and some followers
        $user = User::factory()->create(); // This is the authenticated user
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();

        // Create follow relationships
        Follows::create([
            'follower_id' => $follower1->id,
            'followed_id' => $user->id,
            'is_friend' => false,
        ]);
        Follows::create([
            'follower_id' => $follower2->id,
            'followed_id' => $user->id,
            'is_friend' => false,
        ]);

        // Act as the user
        $this->actingAs($user);

        // Perform the request to retrieve the followers list
        $response = $this->getJson('/api/user/get_follower_list');

        // Assert the response status and JSON structure
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Followers list retreived succesfully.',
                ])
                ->assertJsonStructure([
                    'followers_list' => [
                        '*' => ['id', 'username', 'email', 'profile_picture', 'is_active'] // Adjust this based on the actual structure of User
                    ],
                ]);
    }

    public function test_get_follower_list_returns_empty_list_when_no_followers()
    {
        // Create a user with no followers
        $user = User::factory()->create();

        // Act as the user
        $this->actingAs($user);

        // Perform the request to retrieve the followers list
        $response = $this->getJson('/api/user/get_follower_list');

        // Assert the response is successful with an empty follower list
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Followers list retreived succesfully.',
                    'followers_list' => [],
                ]);
    }

    public function test_get_follower_list_returns_error_if_retrieval_fails()
    {
        // Simulate an error by not authenticating the user
        // The Auth::id() call will fail since there's no authenticated user

        // Perform the request without an authenticated user
        $response = $this->getJson('/api/user/get_follower_list');

        // Assert that the response status is 500 (or adjust if needed)
        $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'No query results for model [App\\Models\\User].', // Adjust based on actual error message
                ]);
    }

    ///////////

    public function test_get_following_list_successfully_retrieves_following_list()
    {
        // Create a user and some followers
        $user = User::factory()->create(); // This is the authenticated user
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();

        // Create follow relationships
        Follows::create([
            'follower_id' => $user->id,
            'followed_id' => $follower1->id,
            'is_friend' => false,
        ]);
        Follows::create([
            'follower_id' => $user->id,
            'followed_id' => $follower2->id,
            'is_friend' => false,
        ]);

        // Act as the user
        $this->actingAs($user);

        // Perform the request to retrieve the followers list
        $response = $this->getJson('/api/user/get_following_list');

        // Assert the response status and JSON structure
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Following list retreived succesfully.',
                ])
                ->assertJsonStructure([
                    'following_list' => [
                        '*' => ['id', 'username', 'email', 'profile_picture', 'is_active'] // Adjust this based on the actual structure of User
                    ],
                ]);
    }

    public function test_get_following_list_returns_empty_list_when_no_followings()
    {
        // Create a user with no followers
        $user = User::factory()->create();

        // Act as the user
        $this->actingAs($user);

        // Perform the request to retrieve the followers list
        $response = $this->getJson('/api/user/get_following_list');

        // Assert the response is successful with an empty follower list
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Following list retreived succesfully.',
                    'following_list' => [],
                ]);
    }

    public function test_get_following_list_returns_error_if_retrieval_fails()
    {
        // Simulate an error by not authenticating the user
        // The Auth::id() call will fail since there's no authenticated user

        // Perform the request without an authenticated user
        $response = $this->getJson('/api/user/get_following_list');

        // Assert that the response status is 500 (or adjust if needed)
        $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'No query results for model [App\\Models\\User].', // Adjust based on actual error message
                ]);
    }
}
