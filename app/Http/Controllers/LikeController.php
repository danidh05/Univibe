<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likePost($postId)
    {
        try {
            $post = Post::find($postId);

            // Check if the user has already liked the post (dislike)
            $dislike_post = Like::where('user_id', Auth::id())
                                  ->where('post_id', $postId)
                                  ->first();

            if ($dislike_post) {
                // Dislike the post (delete the like record)
                $dislike_post->delete();

                // Decrement the likes_count in the Post model
                if ($post->like_count > 0) {
                    $post->decrement('like_count');
                }

                return response()->json(['message' => 'Post Disliked'], 200);
            }

            // Like the post (create a like record)
             Like::create([
                'user_id' => Auth::id(),
                'post_id' => $post->id,
            ]);

            // Increment the likes_count in the Post model
            $post->increment('like_count');

            return response()->json(['message' => 'Post Liked'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to like/dislike post', 'message' => $e->getMessage()], 500);
        }
    }

}
