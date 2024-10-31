<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Repost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

 /**
     * @OA\Schema(
     *     schema="User",
     *     title="User",
     *     description="User model",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID of the user"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Name of the user"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         format="email",
     *         description="Email of the user"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Timestamp when the user was created"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Timestamp when the user was last updated"
     *     )
     * )
     */
class RepostController extends Controller
{

    /**
     * @OA\Post(
     *     path="/posts/{id}/repost",
     *     summary="Repost a post",
     *     description="Allows the authenticated user to repost a specific post. The user cannot repost their own post or repost the same post more than once.",
     *     tags={"Repost"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to repost",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post reposted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post reposted successfully"),
     *             @OA\Property(property="original_post", type="object", ref="#/components/schemas/Post"),
     *             @OA\Property(property="original_poster", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User cannot repost their own post",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You cannot repost your own post")
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
     *         response=409,
     *         description="Duplicate repost",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have already reposted this post")
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
    public function repost($postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Check if the user is the original poster
        if ($post->user_id == Auth::id()) { // Corrected field name
            return response()->json(['message' => 'You cannot repost your own post'], 403);
        }

        // Check if the user already reposted the post
        $existingRepost = Repost::where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->first();

        if ($existingRepost) {
            return response()->json(['message' => 'You have already reposted this post'], 409);
        }

        // Create a new repost entry
        $repost = Repost::create([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
        ]);

        // Return the original post and the user who created it
        return response()->json([
            'message' => 'Post reposted successfully',
            'original_post' => $post,
            'original_poster' => $post->user, // Assuming you have a 'user' relationship on the Post model
        ], 201);
    }


   /**
     * @OA\Delete(
     *     path="/posts/{id}/repost",
     *     summary="Delete a repost",
     *     description="Allows the authenticated user to delete their repost of a specific post.",
     *     tags={"Repost"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to remove repost",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Repost deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Repost deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Repost not found or not authorized to delete",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Repost not found or not authorized to delete")
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
    public function deleteRepost($postId)
    {
        // Find the repost by the current user for the given post
        $repost = Repost::where('user_id', Auth::id())->where('post_id', $postId)->first();

        if (!$repost) {
            return response()->json(['message' => 'Repost not found or not authorized to delete'], 404);
        }

        // Delete the repost
        $repost->delete();

        return response()->json(['message' => 'Repost deleted successfully'], 200);
    }
}
