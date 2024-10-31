<?php

namespace App\Http\Controllers;

use App\Models\GroupChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupMembersController extends Controller
{
    /**
     * @OA\PathItem(
     *     path="/group/members/add",
     *     @OA\Post(
     *         summary="Add a member to the group chat",
     *         tags={"Group Member"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=409, description="Conflict, member limit reached (max 100 members) or already member of the group"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"user_id", "group_chat_id"},
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="group_chat_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
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
            ], 200);
            
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

    /**
     * @OA\PathItem(
     *     path="/group/members/remove",
     *     @OA\Post(
     *         summary="Remove a member from the group chat",
     *         tags={"Group Member"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=404, description="Member not found"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"user_id", "group_chat_id"},
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="group_chat_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
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
                ], 410); // 404 if the resource has already been deleted
            }

            $groupChat->members()->detach($userToRemove);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully!',
            ], 200);
            
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

    /**
     * @OA\PathItem(
     *     path="/group/members/leave",
     *     @OA\Post(
     *         summary="Leave the group chat",
     *         tags={"Group Member"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"group_chat_id"},
     *                 @OA\Property(property="group_chat_id", type="integer", example=1)
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function leave(Request $request){
        try {
            $groupChat = GroupChat::find($request->input('group_chat_id'));

            if ($groupChat->owner_id == Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot leave your own group, you must delete the group.'
                ], 403); // 403 because it is not authorized
            }

            $groupChat->members()->detach(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'You have left successfully!',
            ], 200);
            
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
