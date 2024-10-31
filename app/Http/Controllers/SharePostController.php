<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharePostController extends Controller
{
     /**
     * @OA\Post(
     *     path="/posts/{postId}/share-user",
     *     summary="Share a post with specific users",
     *     description="Allows the authenticated user to share a post with specific users, to a feed, or by generating a link.",
     *     tags={"SharePost"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to share",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="recipient_id", type="array", description="Array of recipient user IDs for sharing with specific users",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(property="share_type", type="string", enum={"user", "feed", "link"}, description="The type of share (user, feed, or link)", example="user"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post shared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post shared successfully."),
     *             @OA\Property(property="post_link", type="string", example="http://example.com/posts/1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
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
        // \Log::error('Validation error: ' . json_encode($e->errors()));
        return response()->json(['errors' => $e->errors()], 422); // Return proper validation error
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => $e->getMessage()], 404);
    } catch (\Exception $e) {
        // \Log::error($e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


 /**
     * @OA\Get(
     *     path="/posts/{postId}/copy-link",
     *     summary="Copy the link of a post",
     *     description="Generates a shareable link for a specific post.",
     *     tags={"SharePost"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to generate a link for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post link generated successfully."),
     *             @OA\Property(property="post_link", type="string", example="http://example.com/posts/1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Post not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred while generating the post link"),
     *             @OA\Property(property="details", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
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
