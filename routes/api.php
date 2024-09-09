<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMembersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/messages/send', [MessageController::class, 'sendPrivateMessage']);
Route::get('/messages/get', [MessageController::class, 'getPrivateMessages']);
Route::put('/messages/update', [MessageController::class, 'updatePrivateMessage']);
Route::delete('/messages/delete', [MessageController::class, 'deletePrivateMessage']);

Route::post('/groups/create', [GroupController::class, 'createGroup']);
Route::get('/groups/get', [GroupController::class, 'getMyGroups']);
Route::put('/groups/update/name', [GroupController::class, 'updateGroupName']);
Route::put('/groups/update/photo', [GroupController::class, 'updateGroupPhoto']); // there's a problem with sending data in "form-data"
Route::delete('/groups/delete', [GroupController::class, 'deleteGroup']);

Route::post('/group/members/add', [GroupMembersController::class, 'add']);
Route::post('/group/members/remove', [GroupMembersController::class, 'remove']); // either admin removing someone or someone removing himself