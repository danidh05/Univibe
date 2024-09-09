<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Response;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $post;
    protected $comment;
    protected $otherUser;


    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and a post for testing
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);

        // Create a comment for testing
        $this->comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'content' => 'Initial comment content'
        ]);
    }


    /** @test */
    public function it_can_show_comments_for_a_post()
    {
        $response = $this->actingAs($this->user)->getJson('/api/show_comment/' . $this->post->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                '*' => ['id', 'post_id', 'user_id', 'content', 'created_at', 'updated_at'],
            ]);
    }

    /** @test */
    public function it_can_add_a_comment_to_a_post()
    {
        $commentData = [
            'content' => 'Test comment content',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/add_comment/' . $this->post->id, $commentData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment($commentData);
    }

    /** @test */
    public function it_can_update_a_comment()
    {
        $updatedData = [
            'content' => 'Updated comment content',
        ];

        $response = $this->actingAs($this->user)->putJson('/api/update_comment/' . $this->comment->id, $updatedData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment($updatedData);
    }

    /** @test */
    public function it_can_delete_a_comment()
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/delete_comment/' . $this->comment->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Comment deleted successfully']);
    }

    /** @test  */
    public function it_cannot_add_comment_if_not_authenticated()
    {
        $commentData = [
            'content' => 'Unauthorized comment',
        ];

        $response = $this->postJson('/api/add_comment/' . $this->post->id, $commentData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test  */
    public function it_cannot_update_comment_if_not_authorized()
    {

        $updatedData = [
            'content' => 'Attempted unauthorized update',
        ];

        $response = $this->actingAs($this->otherUser)->putJson('/api/update_comment/' . $this->comment->id, $updatedData);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test  */
    public function it_cannot_delete_comment_if_not_authorized()
    {

        $response = $this->actingAs($this->otherUser)->deleteJson('/api/delete_comment/' . $this->comment->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
