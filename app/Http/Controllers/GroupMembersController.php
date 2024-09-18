<?php

namespace App\Http\Controllers;

use App\Models\GroupChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupMembersController extends Controller
{
    public function add(Request $request){
        try {

            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $userToAdd = $request->input('user_id');
            $groupChat = GroupChat::find($request->input('group_chat_id'));

            if(count($groupChat->members) == 100){
                return response()->json([
                    'success' => false,
                    'message' => 'The group is full.'
                ], 409); // 409 for conflict
            }

            // Check if the user is already a member of the group
            $isAlreadyMember = $groupChat->members()->where('user_id', $userToAdd)->exists();

            if ($isAlreadyMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this group.'
                ], 409); // 409 for conflict
            }

            $groupChat->members()->attach($userToAdd, ['joined_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully!',
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function remove(Request $request){
        try {
            $request->validate([
                // 'group_chat_id' => 'required|exists:group_chats,id',
                'user_id' => 'required|exists:users,id'
            ]);

            $userToRemove = $request->input('user_id');
            $groupChat = GroupChat::find($request->input('group_chat_id'));

            if ($groupChat->owner_id == $userToRemove) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove yourself (the group owner) from the group.'
                ], 403); // 403 because it is not authorized
            }

            // Check if the user is a member of the group
            $isMember = $groupChat->members()->where('user_id', $userToRemove)->exists();

            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a member of this group.'
                ], 410); // 410 if the resource has already been deleted
            }

            $groupChat->members()->detach($userToRemove);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully!',
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function leave(Request $request){
        try {
            $groupChat = GroupChat::find($request->input('group_chat_id'));
            $groupChat->members()->detach(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'You have left successfully!',
            ], 201);
            
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
