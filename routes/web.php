<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;

Route::get('/', function () {
    return view('welcome');
});

// {{ Post }}
Route::get('/api/show_posts' , [PostController::class , 'index']);
Route::post('/api/add_post' , [PostController::class , 'add_post']);
Route::put('/api/update_post/{id}' , [PostController::class , 'update_post']);
Route::delete('/api/delete_post/{id}' , [PostController::class , 'delete_post']);
Route::get('/api/show_user_post/{id}' , [PostController::class , 'show_user_post']);


// {{  Comment }}
Route::get('/api/show_comment/{postId}' , [CommentController::class , 'show_post_commnent']);
Route::post('/api/add_comment/{postId}' , [CommentController::class , 'add_comment']);
Route::put('/api/update_comment/{commentId}' , [CommentController::class , 'update_comment']);
Route::delete('/api/delete_comment/{commentId}' , [CommentController::class , 'delete_comment']);

// {{ Like }}
Route::post('/api/like_post/{postId}' , [LikeController::class , 'likePost']);
