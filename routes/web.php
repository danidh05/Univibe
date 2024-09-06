<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/api/show_posts' , [PostController::class , 'index']);
Route::post('/api/add_post' , [PostController::class , 'add_post']);
Route::put('/api/update_post/{id}' , [PostController::class , 'update_post']);
Route::delete('/api/delete_post/{id}' , [PostController::class , 'delete_post']);
