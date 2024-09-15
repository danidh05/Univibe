<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Share;
use Illuminate\Http\Request;

class SharePostController extends Controller
{
    public function ShareWithUsers(Request $request, $post_id)
    {
        try {
            // Validate the request for recipient
            $validated = $request->validate([
                'recipient_id' => 'required|array',      // Validate that recipient_id is required and is an array
                'recipient_id.*' => 'exists:users,id',   // Validate that each recipient_id is a valid user
            ]);

            // Check if recipient_id is an array, otherwise convert it to an array
            $recipientIds = $validated['recipient_id'];


            $post = Post::findOrFail($post_id);

            foreach ($recipientIds as $recipientId) {
                // We are removing the check for already shared, so now it can be shared multiple times
                Share::create([
                    'user_id' => Auth::id(),
                    'post_id' => $post->id,
                    'share_type' => 'user',
                    'recipient_id' => $recipientId,
                ]);
            }

            return response()->json([
                'message' => 'Post shared successfully.',
                'post_link' => route('posts.show', ['post' => $post->id])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return error if post not found
            return response()->json(['message' => $e->getMessage()], 404);

        } catch (\Exception $e) {
            // Handle other errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function copyLink($postId)
    {
        try {
            $post = Post::findOrFail($postId);

            $postUrl = route('posts.show', ['post' => $post->id]);

            return response()->json([
                'message' => 'Post link generated successfully.',
                'post_link' => $postUrl
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Post not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while generating the post link',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
