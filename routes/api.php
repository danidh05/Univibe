<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SavePostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// {{ Post }}
Route::get('/show_posts' , [PostController::class , 'index']);
Route::post('/add_post' , [PostController::class , 'add_post']);
Route::put('/update_post/{id}' , [PostController::class , 'update_post']);
Route::delete('/delete_post/{id}' , [PostController::class , 'delete_post']);
Route::get('/show_user_post/{id}' , [PostController::class , 'show_user_post']);


// {{  Comment }}
Route::get('/show_comment/{postId}' , [CommentController::class , 'show_post_commnent']);
Route::post('/add_comment/{postId}' , [CommentController::class , 'add_comment']);
Route::put('/update_comment/{commentId}' , [CommentController::class , 'update_comment']);
Route::delete('/delete_comment/{commentId}' , [CommentController::class , 'delete_comment']);

// {{ Like }}
Route::post('/like_post/{postId}' , [LikeController::class , 'likePost']);


// {{ Poll }}
Route::post('/posts/{postId}/vote', [PollController::class, 'vote']);
// Route::get('/posts/{postId}/poll-results', [PollController::class, 'getPollResults']);

//{{ Save Post }}
Route::post('/save_post/{postId}', [SavePostController::class, 'savePost']);
Route::get('/get_save_post', [SavePostController::class, 'getAllSavePost']);
Route::delete('/delete_save_post/{postId}', [SavePostController::class, 'deleteSavePost']);
