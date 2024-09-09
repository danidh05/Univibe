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
                'group_chat_id' => 'required|exists:group_chats,id',
                'user_id' => 'required|exists:users,id'
            ]);

            // ONLY ADMIN CAN ADD
            // $userId = Auth::id();
            $userId = 1; // Testing Purposes
            $userToAdd = $request->input('user_id');
            $groupChat = GroupChat::find($request->input('group_chat_id'));

            if($groupChat->owner_id !== $userId){
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this group.'
                ], 403);
            }

            // Check if the user is already a member of the group
            $isAlreadyMember = $groupChat->members()->where('user_id', $userToAdd)->exists();

            if ($isAlreadyMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this group.'
                ], 400);
            }

            $groupChat->members()->attach($userToAdd, ['joined_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully!',
            ], 201);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function remove(Request $request){
        try {
            $request->validate([
                'group_chat_id' => 'required|exists:group_chats,id',
                'user_id' => 'required|exists:users,id'
            ]);

            // ONLY ADMIN CAN ADD
            // $userId = Auth::id();
            $userId = 2; // Testing Purposes
            $userToRemove = $request->input('user_id');
            $groupChat = GroupChat::find($request->input('group_chat_id'));

            // either admin remove or user removing himself
            if($groupChat->owner_id !== $userId && $userToRemove !== $userId){
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this group.'
                ], 403);
            }

            if ($groupChat->owner_id == $userToRemove) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove yourself (the group owner) from the group.'
                ], 400);
            }

            // Check if the user is a member of the group
            $isMember = $groupChat->members()->where('user_id', $userToRemove)->exists();

            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a member of this group.'
                ], 400);
            }

            $groupChat->members()->detach($userToRemove);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully!',
            ], 201);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
