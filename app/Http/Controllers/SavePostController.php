<?php

namespace App\Http\Controllers;

use App\Models\SavePost;
use Illuminate\Http\Request;

class SavePostController extends Controller
{
    public function savePost($postId)
    {
        try{

            $savePost = new SavePost();
            $savePost->post_id = $postId;
            $savePost->user_id = auth()->user()->id;
            $savePost->save();

            return response()->json('Post saved successfully', 200);

        }catch(Exception $e){
            return response()->json('message' , $e->getMessage());
        }
    }

    public function getAllSavePost()
    {
        try{

            $savePosts = SavePost::with('user')
                                ->where('user_id', auth()->user()->id)
                                ->orderBy('created_at','desc')
                                ->get();

            return response()->json($savePosts, 200);

        }catch(Exception $e){
            return response()->json('message' , $e->getMessage());
        }
    }

    public function deleteSavePost($postId)
    {
        try{

            $savePost = SavePost::where('post_id', $postId)
                               ->where('user_id', 1)
                               ->first();

            if($savePost){
                $savePost->delete();
                return response()->json('Post deleted from saved list successfully', 200);
            }else{
                return response()->json('Post not found in saved list', 404);
            }

        }catch(Exception $e){
            return response()->json('message' , $e->getMessage());
        }
    }
    
}
