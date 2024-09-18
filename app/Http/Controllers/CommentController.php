<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class CommentController extends Controller
{

    protected $notificationService;

    /**
     * Inject NotificationService into the controller.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    public function show_post_commnent($postId)
    {
        $comments = Comment::with('post')
            ->with('user')
            ->where('post_id', $postId)
            ->latest()
            ->get();

        return response()->json($comments, 200);
    }

    public function add_comment(Request $request, $postId)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'content' => 'required|string|max:255',
            ]);

            $post = Post::find($postId);

            $comment = new Comment();
            $comment->user_id = Auth::id();
            $comment->post_id = $post->id;
            $comment->content = $request->content;
            $comment->save();

            $notification_content = Auth::user()->username . ' commented on your post.';

            // Prepare notification data
            $notification_data = [
                'post_id' => $post->id,
                'comment_id' => $comment->id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->username,
                'comment_content' => $comment->content,
            ];

            // Send the notification using NotificationService
            $this->notificationService->createNotification(
                $post->user_id,                      // The post owner (who will receive the notification)
                'comment',                           // Type of notification
                $notification_content,               // Notification content
                $notification_data,                  // Additional data (comment info)
                $post->user->pusher_channel            // Pusher channel (target post owner)
            );

            return response()->json($comment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add comment', 'message' => $e->getMessage()], 500);
        }
    }



    public function update_comment(Request $request, $commentId)
    {
        try {
            //check if auth
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'content' => 'required|string|max:255',
            ]);

            $comment = Comment::find($commentId); //find comment

            if (!$comment || $comment->user_id != Auth::id()) { //check if comment exist or user auth
                return response()->json(['error' => 'Comment not found or unauthorized'], 404);
            }

            $comment->content = $request->content;
            $comment->save();

            return response()->json($comment, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update comment', 'message' => $e->getMessage()], 500);
        }
    }


    public function delete_comment($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $comment = Comment::find($id);

            if (!$comment || $comment->user_id != Auth::id()) {
                return response()->json(['error' => 'Comment not found or unauthorized'], 403);
            }

            // Get the post ID before deleting the comment
            $postId = $comment->post_id;

            $comment->delete();

            // Decrement the comments count in the Post model
            $post = Post::find($postId);
            if ($post && $post->comment_count > 0) {
                $post->decrement('comment_count'); // Decrement the comment_count field by 1
            }

            return response()->json(['message' => 'Comment deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete comment', 'message' => $e->getMessage()], 500);
        }
    }

}
