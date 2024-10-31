<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\SavePost;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaveControllerTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function it_can_save_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
    ->post("/api/save_post/{$post->id}")
    ->assertStatus(200)
    ->assertJson(['message' => 'Post saved successfully']);

    }

    /** @test */
    public function it_can_get_all_saved_posts()
    {
        $user = User::factory()->create();
        SavePost::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/api/get_save_post')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

   /** @test */
   public function it_can_delete_a_saved_post()
   {
       $user = User::factory()->create();
       $post = Post::factory()->create();
       $savePost = SavePost::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

       $this->actingAs($user)
           ->delete("/api/delete_save_post/{$post->id}")
           ->assertStatus(200)
           ->assertJson(['message' => 'Post deleted from saved list successfully']);
   }

   /** @test */
   public function it_returns_error_when_deleting_nonexistent_saved_post()
   {
       $user = User::factory()->create();
       $post = Post::factory()->create();

       $this->actingAs($user)
           ->delete("/api/delete_save_post/{$post->id}")
           ->assertStatus(404)
           ->assertJson(['message' => 'Post not found in saved list']);
   }
}
