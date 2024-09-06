<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;

class PostController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::orderBy('created_at', 'desc')->get();

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

            return response()->json($post, 200);

        } catch (\Exception $e) {

            // Log::error('Error creating post: '.$e->getMessage());

            return response()->json(['error' => 'Failed to create post'], 500);
        }
    }

    public function show_user_post($id)
    {
        try {
            $post = Post::find($id);

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

            return response()->json($post, 200);

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

            return response()->json('Post deleted successfully', 200);

        } catch (\Exception $e) {
            // Log::error('Error deleting post: '.$e->getMessage());
            return response()->json(['error' => 'Failed to delete post'], 500);
        }
    }

}
