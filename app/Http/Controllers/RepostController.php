<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Repost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepostController extends Controller
{
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
