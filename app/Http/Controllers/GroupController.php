<?php

namespace App\Http\Controllers;

use App\Models\GroupChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function createGroup(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'photo' => 'url|nullable',
            ]);
    
            $group = new GroupChat();
            $group->group_name = $request->input('name');
            $group->group_photo = $request->input('photo');
            $group->owner_id = Auth::id();
            // $group->owner_id = 1; // Testing Purposes
            $group->save();

            // $group->users()->attach(Auth::id(), ['joined_at' => now()]);
            $group->members()->attach(1, ['joined_at' => now()]); // Testing Purposes
    
            return response()->json([
                'success' => true,
                'message' => 'Group created successfully!',
                'group' => $group
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

    public function getMyGroups(){
        try {
            $userId = Auth::id();
            // $userId = 1; // Testing Purposes

            $groups = GroupChat::whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();

            return response()->json([
                'success' => true,
                'groups' => $groups
            ]);
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

    public function updateGroupName(Request $request){
        try {
            $request->validate([
                'group_chat_id' => 'required|exists:group_chats,id',
                'name' => 'required|string|max:255',
            ]);

            $userId = Auth::id();
            // $userId = 1; // Testing Purposes

            $groupChat = GroupChat::find($request->input('group_chat_id'));

            // Check if the current user is the owner of the group chat
            if ($groupChat->owner_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this group.'
                ], 403);
            }

            $groupChat->group_name = $request->input('name');
            $groupChat->save();

            return response()->json([
                'success' => true,
                'message' => 'Group name updated successfully.',
                'group' => $groupChat
            ]);
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

    public function updateGroupPhoto(Request $request)
    {
        try {

            $request->validate([
                'group_chat_id' => 'required|exists:group_chats,id',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $userId = Auth::id();
            // $userId = 1; // Testing Purposes

            $groupChat = GroupChat::find($request->input('group_chat_id'));

            // Check if the current user is the owner of the group chat
            if ($groupChat->owner_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this group.'
                ], 403);
            }

            // Check if a photo is provided and handle file upload
            if ($request->hasFile('photo')) {
                // Delete the old photo if it exists
                if ($groupChat->photo) {
                    Storage::delete($groupChat->photo);
                }

                // Store the new photo
                $photoPath = $request->file('photo')->store('group_photos');

                $groupChat->group_photo = $photoPath;
            }

            $groupChat->save();

            return response()->json([
                'success' => true,
                'message' => 'Group photo updated successfully.',
                'group' => $groupChat
            ]);
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

    public function deleteGroup(Request $request){
        try {
            $request->validate([
                'group_chat_id' => 'required|exists:group_chats,id',
            ]);

            $userId = Auth::id();
            // $userId = 1; // Testing Purposes

            $groupChat = GroupChat::find($request->input('group_chat_id'));

            // Check if the current user is the owner of the group chat
            if ($groupChat->owner_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this group.'
                ], 403);
            }

            // Delete the group chat
            $groupChat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Group chat deleted successfully.',
            ]);

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
}
