<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Response;

class PostTest extends TestCase
{
    /**
     * A basic unit test example.
     */


    protected $user;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_can_return_all_posts()
    {
        $response = $this->actingAs($this->user)->getJson('/api/show_posts');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                '*' => ['id', 'user_id', 'content', 'media_url', 'postType']
            ]);
    }


    /** @test */
    public function it_can_create_a_post()
    {
        $postData = [
            'content' => 'Test content',
            'user_id' => $this->user->id,
            'media_url' => 'http://example.com/media',
            'postType' => 'text'
        ];

        $response = $this->actingAs($this->user)->postJson('/api/add_post', $postData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson($postData);
    }

    /** @test */
    public function it_can_update_a_post()
    {
        $updatedData = [
            'user_id' => $this->user->id,
            'content' => 'Updated content',
            'media_url' => 'http://example.com/new-media',
            'postType' => 'image'
        ];

        $response = $this->actingAs($this->user)->putJson('/api/update_post/' . $this->post->id, $updatedData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson($updatedData);
    }

    /** @test */
    public function it_can_delete_a_post()
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/delete_post/' . $this->post->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function it_can_show_posts_by_user()
    {
        $response = $this->actingAs($this->user)->getJson('/api/show_user_post/' . $this->user->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                [
                    'id' => $this->post->id,
                    'user_id' => $this->post->user_id,
                    'content' => $this->post->content,
                    'media_url' => $this->post->media_url, // Update this line
                    'postType' => $this->post->postType,
                ]
            ]);
    }
}