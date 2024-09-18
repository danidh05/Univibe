<?php

use App\Http\Controllers\FollowsController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMembersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CheckGroupMember;
use App\Http\Middleware\CheckGroupOwner;
use App\Http\Middleware\UserIdValidation;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/messages/send', [MessageController::class, 'sendPrivateMessage']);
Route::get('/messages/get/{user_id}', [MessageController::class, 'getPrivateMessages']);
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

Route::post('/user/follow', [FollowsController::class, 'follow'])->middleware(UserIdValidation::class);
Route::post('/user/unfollow', [FollowsController::class, 'unfollow'])->middleware(UserIdValidation::class);
Route::get('/user/is_following/{user_id}', [FollowsController::class, 'is_following']);
Route::get('/user/is_followed/{user_id}', [FollowsController::class, 'is_followed']);
Route::get('/user/get_follower_list', [FollowsController::class, 'get_follower_list']);
Route::get('/user/get_following_list', [FollowsController::class, 'get_following_list']);

Route::post('/user/send_friend_request', [FriendRequestController::class, 'send_friend_request'])->middleware(UserIdValidation::class);
Route::post('/user/accept_friend_request', [FriendRequestController::class, 'accept_friend_request']);
Route::post('/user/reject_friend_request', [FriendRequestController::class, 'reject_friend_request']);
Route::post('/user/cancel_friend_request', [FriendRequestController::class, 'cancel_friend_request']);
Route::delete('/user/remove_friend', [FriendRequestController::class, 'remove_friend'])->middleware(UserIdValidation::class);
Route::get('/user/get_friends_list', [FriendRequestController::class, 'get_friends_list']);
Route::get('/user/get_all_sent_friend_requests', [FriendRequestController::class, 'get_all_sent_friend_requests']);
Route::get('/user/get_all_received_friend_requests', [FriendRequestController::class, 'get_all_received_friend_requests']);
Route::get('/user/is_friend/{user_id}', [FriendRequestController::class, 'is_friend']);

Route::post('/user/update', [ProfileController::class, 'updateProfile']);