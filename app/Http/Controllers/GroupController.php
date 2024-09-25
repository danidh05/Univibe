<?php

namespace App\Http\Controllers;

use App\Models\GroupChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    /**
     * @OA\PathItem(
     *     path="/groups/create",
     *     @OA\Post(
     *         summary="Create a group",
     *         tags={"Group Chat"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="My Group", description="The name of the group"),
     *                 @OA\Property(property="photo", type="string", format="binary", description="Optional group photo (jpeg, png, jpg)")
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function createGroup(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,max:2048', // only allows jpeg png and jpg
            ]);
    
            $group = new GroupChat();
            $group->group_name = $request->input('name');
            $filePath = $request->file('photo')->store('group_pictures', 'public');
            $group->group_photo = $filePath;
            $group->owner_id = Auth::id();
            $group->save();

            $group->members()->attach(Auth::id(), ['joined_at' => now()]);
    
            return response()->json([
                'success' => true,
                'message' => 'Group created successfully!',
                'group' => $group
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
     *     path="/groups/get",
     *     @OA\Get(
     *         summary="Gets all the group chats you are in",
     *         tags={"Group Chat"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function getMyGroups(){
        try {
            $userId = Auth::id();

            $groups = GroupChat::whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();

            return response()->json([
                'success' => true,
                'groups' => $groups
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
     *     path="/groups/update/name",
     *     @OA\Put(
     *         summary="Updates the name of the group chat",
     *         tags={"Group Chat"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"name", "group_chat_id"},
     *                 @OA\Property(property="name", type="string", example="New Group Name"),
     *                 @OA\Property(property="group_chat_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function updateGroupName(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $groupChat = GroupChat::find($request->input('group_chat_id'));

            $groupChat->group_name = $request->input('name');
            $groupChat->save();

            return response()->json([
                'success' => true,
                'message' => 'Group name updated successfully.',
                'group' => $groupChat
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
     *     path="/groups/update/photo",
     *     @OA\Put(
     *         summary="Updates the photo of the group chat",
     *         tags={"Group Chat"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"photo", "group_chat_id"},
     *                 @OA\Property(property="photo", type="string", format="binary"),
     *                 @OA\Property(property="group_chat_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function updateGroupPhoto(Request $request)
    {
        try {

            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,max:2048', // only allows jpeg png and jpg
            ]);

            $groupChat = GroupChat::find($request->input('group_chat_id'));

            // Check if a photo is provided and handle file upload
            if ($request->hasFile('photo')) {
                // Delete the old photo if it exists !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! //
                if ($groupChat->photo) {
                    // Storage::delete($groupChat->photo);
                    Storage::disk('public')->delete($groupChat->photo);
                }

                // Store the new photo
                $photoPath = $request->file('photo')->store('group_pictures','public');

                $groupChat->group_photo = $photoPath;
            }

            $groupChat->save();

            return response()->json([
                'success' => true,
                'message' => 'Group photo updated successfully.',
                'group' => $groupChat
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
     *     path="/groups/delete",
     *     @OA\Delete(
     *         summary="Deletes a group chat",
     *         tags={"Group Chat"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"group_chat_id"},
     *                 @OA\Property(property="group_chat_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function deleteGroup(Request $request){
        try {
            $groupChat = GroupChat::find($request->input('group_chat_id'));

            // Delete the group chat
            $groupChat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Group chat deleted successfully.',
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
