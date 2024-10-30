<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

/**
 * @OA\Schema(
 *     schema="Comment",
 *     title="Comment",
 *     description="A comment made on a post",
 *     @OA\Property(property="id", type="integer", description="Unique identifier for the comment"),
 *     @OA\Property(property="post_id", type="integer", description="ID of the post the comment belongs to"),
 *     @OA\Property(property="user_id", type="integer", description="ID of the user who made the comment"),
 *     @OA\Property(property="content", type="string", description="Content of the comment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date and time when the comment was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date and time when the comment was last updated")
 * )
 */


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

/**
     * @OA\Get(
     *     path="/show_comment/{postId}",
     *     summary="Get Comments on a Post",
     *     description="Retrieve all comments on a specific post.",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to retrieve comments for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved comments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    public function show_post_commnent($postId)
    {
        $comments = Comment::with('post')
            ->with('user')
            ->where('post_id', $postId)
            ->latest()
            ->get();

        return response()->json($comments, 200);
    }

     /**
     * @OA\Post(
     *     path="/add_comment/{postId}",
     *     summary="Add a Comment to a Post",
     *     description="Add a new comment to a specific post. Sends a notification to the post owner.",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to comment on",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", description="Content of the comment", example="This is a comment.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment added successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Comment")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add comment"
     *     )
     * )
     */
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


 /**
     * @OA\Put(
     *     path="/update_comment/{commentId}",
     *     summary="Update a Comment",
     *     description="Edit an existing comment. Only the original commenter can edit.",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="commentId",
     *         in="path",
     *         description="ID of the comment to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", description="Updated content of the comment", example="This is an updated comment.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Comment")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found or unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update comment"
     *     )
     * )
     */
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


     /**
     * @OA\Delete(
     *     path="/delete_comment/{commentId}",
     *     summary="Delete a Comment",
     *     description="Delete an existing comment. Only the original commenter can delete.",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="commentId",
     *         in="path",
     *         description="ID of the comment to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Comment not found or unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete comment"
     *     )
     * )
     */
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
