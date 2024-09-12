<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMembersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Middleware\CheckGroupMember;
use App\Http\Middleware\CheckGroupOwner;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/messages/send', [MessageController::class, 'sendPrivateMessage']);
Route::get('/messages/get', [MessageController::class, 'getPrivateMessages']);
Route::put('/messages/update', [MessageController::class, 'updatePrivateMessage']);
Route::delete('/messages/delete', [MessageController::class, 'deletePrivateMessage']);

Route::post('/groups/create', [GroupController::class, 'createGroup']);
Route::get('/groups/get', [GroupController::class, 'getMyGroups']);
Route::put('/groups/update/name', [GroupController::class, 'updateGroupName'])->middleware(CheckGroupOwner::class);
Route::put('/groups/update/photo', [GroupController::class, 'updateGroupPhoto'])->middleware(CheckGroupOwner::class); // there's a problem with sending data in "form-data"
Route::delete('/groups/delete', [GroupController::class, 'deleteGroup'])->middleware(CheckGroupOwner::class);

Route::post('/group/members/add', [GroupMembersController::class, 'add'])->middleware(CheckGroupOwner::class);
Route::post('/group/members/remove', [GroupMembersController::class, 'remove'])->middleware(CheckGroupOwner::class); // either admin removing someone or someone removing himself
Route::post('/group/members/leave', [GroupMembersController::class, 'leave'])->middleware(CheckGroupMember::class);