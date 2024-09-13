<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerficationController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::get('/email/verify/{id}/{hash}', [EmailVerficationController::class, "verfiy"])->name('verification.verify');


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('userAdmin')->group(function () {
        Route::get('/admin', [UserController::class, 'getAdminInfo']);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
