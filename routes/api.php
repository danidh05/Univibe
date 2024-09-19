<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\EmailVerficationController;
use App\Http\Controllers\ReportController;
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
    Route::post('/blockUser', [BlockController::class, 'blockUser']);
    Route::put('/unblockUser', [BlockController::class, 'unblockUser']);

    Route::get('/getBlockedAccounts', [BlockController::class, 'getBlockedAccounts']);
    Route::get('/getMyReports', [ReportController::class, 'getMyReports']);
    Route::post('/reportUser', [ReportController::class, 'reportUser']);

    Route::middleware('userAdmin')->group(function () {
        Route::get('/admin', [UserController::class, 'getAdminInfo']);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
