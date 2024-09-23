<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShareControllerTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_share_post_with_users()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($user)
            ->post("/api/posts/{$post->id}/share-user", [
                'recipient_id' => [$recipient->id],
                'share_type' => 'user'
            ])
            ->assertStatus(200)
            ->assertJson(['message' => 'Post shared successfully.']);
    }

    /** @test */
    public function it_can_share_post_to_feed()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post("/api/posts/{$post->id}/share-user", [
                'share_type' => 'feed'
            ])
            ->assertStatus(200)
            ->assertJson(['message' => 'Post shared successfully.']);
    }

    /** @test */
    public function it_can_share_post_via_link()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post("/api/posts/{$post->id}/share-user", [
                'share_type' => 'link'
            ])
            ->assertStatus(200)
            ->assertJson(['message' => 'Post shared successfully.']);
    }

   /** @test */
public function it_fails_when_sharing_user_post_without_recipients()
{
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->post("/api/posts/{$post->id}/share-user", [
            'share_type' => 'user' // No recipient ID
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['recipient_id']);
}

}
