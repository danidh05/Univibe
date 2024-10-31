<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\SavePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
     * @OA\Schema(
     *     schema="SavePost",
     *     title="SavePost",
     *     description="A saved post by the user",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Unique identifier of the saved post"
     *     ),
     *     @OA\Property(
     *         property="post_id",
     *         type="integer",
     *         description="ID of the original post"
     *     ),
     *     @OA\Property(
     *         property="user_id",
     *         type="integer",
     *         description="ID of the user who saved the post"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Date and time when the post was saved"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Date and time when the post was last updated"
     *     )
     * )
     */
class SavePostController extends Controller
{

     /**
     * @OA\Post(
     *     path="/save_post/{postId}",
     *     summary="Save a post",
     *     description="Save a post to the user's saved posts list",
     *     tags={"SavedPosts"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to save",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post saved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to save post",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function savePost($postId)
{
    try {
        $savePost = new SavePost();
        $savePost->post_id = $postId;
        $savePost->user_id = Auth::user()->id;
        $savePost->save();

        // Return a JSON response instead of a string
        return response()->json(['message' => 'Post saved successfully'], 200);

    } catch (Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}


    /**
     * @OA\Get(
     *     path="/get_save_post",
     *     summary="Get all saved posts",
     *     description="Retrieve all posts saved by the authenticated user",
     *     tags={"SavedPosts"},
     *     @OA\Response(
     *         response=200,
     *         description="List of saved posts",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SavePost"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve saved posts",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function getAllSavePost()
    {
        try{

            $savePosts = SavePost::with('user')
                                ->where('user_id', Auth::user()->id)
                                ->orderBy('created_at','desc')
                                ->get();

            return response()->json($savePosts, 200);

        }catch(Exception $e){
            return response()->json(['message' , $e->getMessage()] , 500);
        }
    }
 /**
     * @OA\Delete(
     *     path="/delete_save_post/{postId}",
     *     summary="Delete a saved post",
     *     description="Remove a post from the user's saved posts list",
     *     tags={"SavedPosts"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to delete from saved posts",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted from saved list successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post deleted from saved list successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found in saved list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found in saved list")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete saved post",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function deleteSavePost($postId)
    {
        try {
            $savePost = SavePost::where('post_id', $postId)
                                ->where('user_id', Auth::user()->id)
                                ->first();

            if ($savePost) {
                $savePost->delete();
                // Return a structured JSON response
                return response()->json(['message' => 'Post deleted from saved list successfully'], 200);
            } else {
                // Return a structured JSON response
                return response()->json(['message' => 'Post not found in saved list'], 404);
            }

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
