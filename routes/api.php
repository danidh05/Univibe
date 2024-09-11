<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerficationController;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/email/verify/{id}/{hash}', [EmailVerficationController::class, "verfiy"])->name('verification.verify');


// Route::get('/email/resend', function (Request $request) {
//     /** @var \App\Models\User $user */
//     $user = $request->user();


//     if ($user->hasVerifiedEmail()) {
//         return response()->json(['message' => 'Your email is already verified.'], 400);
//     }

//     $user->sendEmailVerificationNotification();

//     return response()->json(['message' => 'Verification email has been resent.']);
// })->middleware(['auth:sanctum', 'throttle:6,1']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
