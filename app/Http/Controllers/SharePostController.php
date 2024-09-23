<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharePostController extends Controller
{
    public function ShareWithUsers(Request $request, $post_id)
{
    try {
        // Validate the request
        $validated = $request->validate([
            'recipient_id' => 'required_if:share_type,user|array', // Recipient ID required for 'user' type
            'recipient_id.*' => 'exists:users,id',
            'share_type' => 'required|in:user,feed,link', // Ensure only valid share types
        ]);

        $shareType = $validated['share_type'];
        $post = Post::findOrFail($post_id);

        if ($shareType === 'user') {
            foreach ($validated['recipient_id'] as $recipientId) {
                Share::create([
                    'user_id' => Auth::id(),
                    'post_id' => $post->id,
                    'share_type' => $shareType,
                    'recipient_id' => $recipientId,
                ]);
            }
        } else {
            Share::create([
                'user_id' => Auth::id(),
                'post_id' => $post->id,
                'share_type' => $shareType,
            ]);
        }

        return response()->json([
            'message' => 'Post shared successfully.',
            'post_link' => route('posts.show', ['post' => $post->id])
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log validation error for debugging
        \Log::error('Validation error: ' . json_encode($e->errors()));
        return response()->json(['errors' => $e->errors()], 422); // Return proper validation error
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => $e->getMessage()], 404);
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
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
