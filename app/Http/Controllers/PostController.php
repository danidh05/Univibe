<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use Illuminate\Http\Response;
use App\Models\PollOption;


class PostController extends Controller
{
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

    public function add_post(PostRequest $request)
    {
        try {
            $post = Post::create($request->validated());

            if ($post->postType === 'poll') {
                foreach ($request->poll['options'] as $option) {
                    PollOption::create([
                        'post_id' => $post->id,
                        'option' => $option
                    ]);
                }
            }

            return response()->json($post,  Response::HTTP_CREATED);

        } catch (\Exception $e) {

            // Log::error('Error creating post: '.$e->getMessage());

            return response()->json(['error' => 'Failed to create post'], 500);
        }
    }

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

    public function update_post(PostRequest $request, $id)
    {
        try {
            $post = Post::find($id);

            $post->update($request->validated());

            return response()->json($post, Response::HTTP_CREATED);

        } catch (\Exception $e) {

            // Log::error('Error updating post: '.$e->getMessage());
            return response()->json(['error' => 'Failed to update post'], 500);
        }
    }

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
