<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\PollOption;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
public function it_can_create_text_post()
{
    $postData = [
        'content' => 'Test content',
        'user_id' => $this->user->id,
        'media_url' => null,
        'postType' => 'text',
    ];

    $response = $this->actingAs($this->user)->postJson('/api/add_post', $postData);

    $response->assertStatus(Response::HTTP_CREATED)
             ->assertJson($postData);
}

/** @test */
public function it_can_create_image_post()
{
    $postData = [
        'content' => 'Test content',
        'user_id' => $this->user->id,
        'media_url' => 'http://example.com/media',
        'postType' => 'image',
    ];

    $response = $this->actingAs($this->user)->postJson('/api/add_post', $postData);

    $response->assertStatus(Response::HTTP_CREATED)
             ->assertJson($postData);
}

/** @test */
public function it_can_create_video_post()
{
    $postData = [
        'content' => 'Test content',
        'user_id' => $this->user->id,
        'media_url' => 'http://example.com/media',
        'postType' => 'video',
    ];

    $response = $this->actingAs($this->user)->postJson('/api/add_post', $postData);

    $response->assertStatus(Response::HTTP_CREATED)
             ->assertJson($postData);
}

/** @test */
public function it_can_create_poll_post()
{
    // Create a user
    $this->user = User::factory()->create();

    // Prepare post data for a poll post
    $postData = [
        'content' => 'This is a poll post',
        'user_id' => $this->user->id,
        'postType' => 'poll',
        'poll' => [
            'options' => ['Option 1', 'Option 2']
        ],
    ];

    // Send the request to create a poll post
    $response = $this->actingAs($this->user)->postJson('/api/add_post', $postData);

    // Dump the response if you need to debug
    $response->dump(); // Useful for debugging, remove once resolved

    // Assert the post was created successfully
    $response->assertStatus(Response::HTTP_CREATED);

    // Assert that the response JSON contains the correct post data
    $response->assertJsonFragment([
        'content' => 'This is a poll post',
        'postType' => 'poll',
    ]);

    // Assert that poll options are saved in the database
    $this->assertDatabaseHas('poll_options', [
        'post_id' => $response->json('id'), // Access the post ID from the response
        'option' => 'Option 1',
    ]);

    $this->assertDatabaseHas('poll_options', [
        'post_id' => $response->json('id'),
        'option' => 'Option 2',
    ]);
}


/** @test */
public function it_can_update_text_post()
{
    $updatedData = [
        'user_id' => $this->user->id,
        'content' => 'Updated content',
        'media_url' => null,
        'postType' => 'text',
    ];

    $response = $this->actingAs($this->user)->putJson('/api/update_post/'.$this->post->id, $updatedData);

    $response->assertStatus(Response::HTTP_CREATED)
             ->assertJson($updatedData);
}

/** @test */
public function it_can_update_image_post()
{
    $updatedData = [
        'user_id' => $this->user->id,
        'content' => 'Updated content',
        'media_url' => 'http://example.com/new-media',
        'postType' => 'image',
    ];

    $response = $this->actingAs($this->user)->putJson('/api/update_post/'.$this->post->id, $updatedData);

    $response->assertStatus(Response::HTTP_CREATED)
             ->assertJson($updatedData);
}

/** @test */
public function it_can_update_video_post()
{
    $updatedData = [
        'user_id' => $this->user->id,
        'content' => 'Updated content',
        'media_url' => 'http://example.com/new-media',
        'postType' => 'video',
    ];

    $response = $this->actingAs($this->user)->putJson('/api/update_post/'.$this->post->id, $updatedData);

    $response->assertStatus(Response::HTTP_CREATED)
             ->assertJson($updatedData);
}

/** @test */
public function it_can_update_poll_post()
{
    // Create a user and a post
    $this->user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $this->user->id,
        'content' => 'Old poll content',
        'postType' => 'poll',
    ]);

    // Create some poll options
    PollOption::create(['post_id' => $post->id, 'option' => 'Old Option 1']);
    PollOption::create(['post_id' => $post->id, 'option' => 'Old Option 2']);

    // Prepare updated post data
    $updatedData = [
        'content' => 'Updated poll content',
        'user_id' => $this->user->id,
        'postType' => 'poll',
        'poll' => [
            'options' => ['New Option 1', 'New Option 2']
        ],
    ];

    // Send the PUT request to update the post
    $response = $this->actingAs($this->user)->putJson('/api/update_post/' . $post->id, $updatedData);

    // Dump the response to see what's happening (for debugging)
    $response->dump(); // This will show the response output

    // Dump the database state for poll_options to inspect the state of the database

    // Assert the post was updated successfully
    $response->assertStatus(Response::HTTP_CREATED);

    // Assert that the content was updated
    $response->assertJsonFragment([
        'content' => 'Updated poll content',
        'postType' => 'poll',
    ]);

    // Assert that old poll options are no longer in the database
    $this->assertDatabaseMissing('poll_options', [
        'post_id' => $post->id,
        'option' => 'Old Option 1',
    ]);

    // Assert that new poll options are saved in the database
    $this->assertDatabaseHas('poll_options', [
        'post_id' => $post->id,
        'option' => 'New Option 1',
    ]);

    $this->assertDatabaseHas('poll_options', [
        'post_id' => $post->id,
        'option' => 'New Option 2',
    ]);
}

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