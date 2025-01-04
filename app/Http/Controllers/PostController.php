<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="Post",
 *     title="Post",
 *     description="A model representing a blog post",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier for the post"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title of the post"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Content of the post"
 *     ),
 *     @OA\Property(
 *         property="postType",
 *         type="string",
 *         description="Type of the post (e.g., normal, poll)"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time when the post was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time when the post was last updated"
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="string",
 *         description="Author of the post"
 *     )
 * )
 */


class PostController extends Controller
{
/**
 * @OA\Get(
 *     path="/posts/{post}",
 *     summary="Get a single post",
 *     description="Retrieve details of a single post by its ID",
 *     tags={"Posts"},
 *     @OA\Parameter(
 *         name="post",
 *         in="path",
 *         description="ID of the post",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful retrieval",
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="content", type="string"),
 *             @OA\Property(property="created_at", type="string", format="date"),
 *             @OA\Property(property="author", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Post not found"
 *     )
 * )
 */
    public function show(Post $post)
    {

        return response()->json([
            'title' => $post->title,
            'content' => $post->content,
            'created_at' => $post->created_at->format('M d, Y'),
            'author' => $post->user->name // Assuming the Post model has a relationship with User
        ], 200);
    }

/**
 * @OA\Get(
 *     path="/show_posts",
 *     summary="Get list of posts",
 *     description="Retrieve a list of all posts in descending order of creation",
 *     tags={"Posts"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful retrieval",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to retrieve posts"
 *     )
 * )
 */
    public function index()
    {
        try {
            $posts = Post::with('pollOptions')->orderBy('created_at', 'desc')->get();

            return response()->json($posts, 200);

        } catch (\Exception $e) {

            // Log::error('Error fetching posts: '.$e->getMessage());
            return response()->json(['error' => 'Failed to retrieve posts'], 500);
        }
    }

/**
 * @OA\Post(
 *     path="/add_post",
 *     summary="Create a new post",
 *     description="Create a new post and optionally add poll options if the postType is 'poll'. The 'poll_options' field is only required and enabled when 'postType' is 'poll'.",
 *     tags={"Posts"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Post object that needs to be added",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="title",
 *                     type="string",
 *                     description="Title of the post",
 *                     example="New Post Title"
 *                 ),
 *                 @OA\Property(
 *                     property="content",
 *                     type="string",
 *                     description="Content of the post",
 *                     example="This is the content of the post."
 *                 ),
 *                 @OA\Property(
 *                     property="postType",
 *                     type="string",
 *                     description="The type of post (e.g., text, image, video, poll). If 'poll', you need to provide poll options.",
 *                     example="poll",
 *                     enum={"text", "image", "video", "poll"}
 *                 ),
 *                 @OA\Property(
 *                     property="poll_options",
 *                     type="array",
 *                     @OA\Items(type="string"),
 *                     description="Options for the poll. Only required if 'postType' is 'poll'.",
 *                     example={"Option 1", "Option 2"}
 *                 ),
 *                 @OA\Property(
 *                     property="user_id",
 *                     type="integer",
 *                     description="ID of the user creating the post.",
 *                     example=1
 *                 ),
 *                 @OA\Property(
 *                     property="image",
 *                     type="file",
 *                     description="Image file for the post if postType is 'image'.",
 *                     format="binary"
 *                 ),
 *                 @OA\Property(
 *                     property="video",
 *                     type="file",
 *                     description="Video file for the post if postType is 'video'.",
 *                     format="binary"
 *                 ),
 *                 required={"title", "content", "postType", "user_id"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Post created",
 *         @OA\JsonContent(ref="#/components/schemas/Post")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to create post"
 *     )
 * )
 */
public function add_post(PostRequest $request)
{
    // Validation rules based on the postType
    $request->validate([
        'content' => 'required|string',
        'postType' => 'required|in:text,image,video,poll',
        'poll.options' => 'required_if:postType,poll|array',  // Update to 'poll.options'
        'poll.options.*' => 'string',  // Update to 'poll.options.*'
        'image' => 'required_if:postType,image|file|mimes:jpg,png,jpeg|max:2048',
        'video' => 'required_if:postType,video|file|mimes:mp4,mov,avi|max:10240',
    ]);

    try {
        // Prepare post data from validated input
        $postData = $request->validated();
        $postData['user_id'] = $request->user()->id;  // Attach the authenticated user

        // Handle file uploads for image or video depending on the postType
        if ($request->postType === 'image' && $request->hasFile('image')) {
            $postData['media_url'] = $request->file('image')->store('images');  // Save image in 'images' directory
        } elseif ($request->postType === 'video' && $request->hasFile('video')) {
            $postData['media_url'] = $request->file('video')->store('videos');  // Save video in 'videos' directory
        } else {
            $postData['media_url'] = null;  // Set to null if not an image or video postType
        }

        // Create the post in the database
        $post = Post::create($postData);

        // If postType is 'poll', handle the poll options
        if ($request->postType === 'poll') {
            // Debugging: Check if poll.options is present
            \Log::info('Poll options:', $request->input('poll.options'));

            foreach ($request->input('poll.options') as $option) {
                // Debugging: Check each option
                \Log::info('Saving poll option:', ['option' => $option]);

                PollOption::create([
                    'post_id' => $post->id, // Ensure this is set correctly
                    'option' => $option,
                    'votes' => 0, // Initialize votes to 0
                ]);
            }
        }

        // Prepare the response data
        $responseData = [
            'id' => $post->id,
            'content' => $post->content,
            'user_id' => $post->user_id,
            'postType' => $post->postType,
            'media_url' => $post->media_url,
        ];

        // Include poll options in the response if the postType is 'poll'
        if ($post->postType === 'poll') {
            $responseData['poll_options'] = $request->input('poll.options');
        }

        return response()->json($responseData, Response::HTTP_CREATED);

    } catch (\Exception $e) {
        // Log the exception for debugging
        \Log::error('Error creating post: ' . $e->getMessage());

        // Return a 500 error response
        return response()->json(['error' => 'Failed to create post: ' . $e->getMessage()], 500);
    }
}

    /**
 * @OA\Get(
 *     path="/show_user_post/{id}",
 *     summary="Get posts by user",
 *     description="Retrieve all posts made by a specific user",
 *     tags={"Posts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of user's posts",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User or posts not found"
 *     )
 * )
 */
    public function show_user_post($id)
    {
        try {
            $user = User::find($id);
            $post = Post::where('user_id' , $user->id)
                         ->orderBy('created_at', 'desc')->get();

            return response()->json($post, 200);

        } catch (\Exception $e) {

            // Log::error('Error fetching post: '.$e->getMessage());
            return response()->json(['error' => 'Post not found'], 404);
        }
    }

    /**
 * @OA\Put(
 *     path="/update_post/{id}",
 *     summary="Update a post",
 *     description="Update an existing post and optionally update poll options if it's a poll",
 *     tags={"Posts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the post to update",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "content"},
 *             @OA\Property(property="title", type="string", example="Updated Post Title"),
 *             @OA\Property(property="content", type="string", example="Updated content of the post"),
 *             @OA\Property(property="postType", type="string", example="poll"),
 *             @OA\Property(property="poll", type="object",
 *                 @OA\Property(property="options", type="array", @OA\Items(type="string"))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Post updated",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Post not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update post"
 *     )
 * )
 */
    public function update_post(PostRequest $request, $id)
    {
        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            // Update the post itself
            $post->update($request->validated());

            // Handle poll options if the postType is 'poll'
            if ($post->postType === 'poll' && isset($request->poll['options'])) {
                // Delete old poll options
                PollOption::where('post_id', $post->id)->delete();

                // Add updated poll options
                foreach ($request->poll['options'] as $option) {
                    PollOption::create([
                        'post_id' => $post->id,
                        'option' => $option
                    ]);
                }
            }

            return response()->json($post->load('pollOptions'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            // Log::error('Error updating post: '.$e->getMessage());
            return response()->json(['error' => 'Failed to update post'], 500);
        }
    }

    /**
 * @OA\Delete(
 *     path="/delete_post/{id}",
 *     summary="Delete a post",
 *     description="Delete an existing post by its ID",
 *     tags={"Posts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the post to delete",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Post deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Post not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to delete post"
 *     )
 * )
 */
    public function delete_post($id)
    {
        try {
            $post = Post::find($id);

            $post->delete();

            return response()->json('Post deleted successfully',  Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {
            // Log::error('Error deleting post: '.$e->getMessage());
            return response()->json(['error' => 'Failed to delete post'], 500);
        }
    }

}