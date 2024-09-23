<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Repost;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepostControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_repost_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post("/api/posts/{$post->id}/repost")
            ->assertStatus(201)
            ->assertJsonStructure(['message', 'original_post', 'original_poster']);
    }

    /** @test */
public function it_cannot_repost_own_post()
{
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post("/api/posts/{$post->id}/repost")
        ->assertStatus(403)
        ->assertJson(['message' => 'You cannot repost your own post']);
}


    /** @test */
    public function it_can_delete_a_repost()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $repost = Repost::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->actingAs($user)
            ->delete("/api/posts/{$post->id}/repost")
            ->assertStatus(200)
            ->assertJson(['message' => 'Repost deleted successfully']);
    }

    /** @test */
    public function it_returns_error_for_deleting_nonexistent_repost()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->delete("/api/posts/{$post->id}/repost")
            ->assertStatus(404)
            ->assertJson(['message' => 'Repost not found or not authorized to delete']);
    }
}
