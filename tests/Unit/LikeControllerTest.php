<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and a post for testing
        $this->user = User::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'like_count' => 0,
        ]);
    }

    /** @test */
    public function it_can_like_a_post()
    {
        // Acting as the created user
        $response = $this->actingAs($this->user)->postJson('/api/like_post/' . $this->post->id);

        // Check that the like was successful and the like count increased
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Post Liked']);

        // Check that a Like entry was created in the database
        $this->assertDatabaseHas('likes', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);

        // Check that the post's like_count was incremented
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'like_count' => 1,
        ]);
    }

    /** @test */
    public function it_can_dislike_a_post()
    {
        // First, like the post
        Like::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);
        $this->post->increment('like_count');

        // Now dislike the post
        $response = $this->actingAs($this->user)->postJson('/api/like_post/' . $this->post->id);

        // Check that the dislike was successful and the like count decreased
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Post Disliked']);

        // Check that the Like entry was removed from the database
        $this->assertDatabaseMissing('likes', [
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);

        // Check that the post's like_count was decremented
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'like_count' => 0,
        ]);
    }

 
}
