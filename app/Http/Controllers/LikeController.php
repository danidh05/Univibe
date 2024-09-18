<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class LikeController extends Controller
{
    protected $notificationService;

    /**
     * Inject NotificationService into the controller.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function likePost($postId)
    {
        try {
            // Get the authenticated user
            $authenticated_user = Auth::user();

            // Find the post being liked
            $post = Post::find($postId);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            // Check if the user has already liked the post (dislike scenario)
            $dislike_post = Like::where('user_id', $authenticated_user->id)
                                ->where('post_id', $postId)
                                ->first();

            if ($dislike_post) {
                // Dislike the post (delete the like record)
                $dislike_post->delete();

                // Decrement the like count in the Post model
                if ($post->like_count > 0) {
                    $post->decrement('like_count');
                }

                return response()->json(['message' => 'Post Disliked'], 200);
            }

            // Like the post (create a like record)
            Like::create([
                'user_id' => $authenticated_user->id,
                'post_id' => $post->id,
            ]);

            // Increment the like count in the Post model
            $post->increment('like_count');

            // Create notification content
            $notification_content = "{$authenticated_user->username} liked your post.";

            // Define notification data to pass
            $notification_data = [
                'post_id' => $post->id,
                'user_id' => $authenticated_user->id,
                'user_name' => $authenticated_user->username,
            ];

            // Send the notification using NotificationService
            $this->notificationService->createNotification(
                $post->user_id,                      // The post owner (who will receive the notification)
                'like',                              // Type of notification
                $notification_content,               // Content of the notification
                $notification_data,                  // Additional data
                'posts.' . $post->user_id            // Pusher channel (target post owner)
            );

            return response()->json(['message' => 'Post Liked and Notification Sent'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to like/dislike post', 'message' => $e->getMessage()], 500);
        }
    }
}
