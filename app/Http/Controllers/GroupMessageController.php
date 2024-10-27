<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Models\GroupChat;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GroupMessageController extends Controller
{
    /**
     * @OA\PathItem(
     *     path="/group/messages/send",
     *     @OA\Post(
     *         summary="Send a group message",
     *         tags={"Group Message"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="group_id", type="integer", example=2, description="The ID of the group receiving the message"),
     *                 @OA\Property(property="content", type="string", example="Hello, how are you?", description="The content of the message"),
     *                 @OA\Property(property="media", type="string", format="binary", description="Optional media file to send with the message")
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function sendGroupMessage(Request $request){
        try {
            $request->validate([
                'group_id' => 'required|exists:group_chats,id',
                'content' => 'required|string|max:255',
                'media' => 'nullable|mimes:jpeg,png,jpg,mp4,mov,avi|max:20480',
            ]);

            // $receiving_user = User::find($request->input('receiver_id'));
            $receiving_group = GroupChat::find($request->input('group_id'));
            $string = app('profanityFilter')->filter($request->input('content'));

            // Handle media upload
            $mediaUrl = null;
            $messageType = 'text';

            if ($request->hasFile('media')) {
                // Store the media in the public/messages folder
                $mediaPath = $request->file('media')->store('group_messages', 'public');
                // Get the public URL of the uploaded media
                $mediaUrl = $mediaPath;
                // Set message type based on the file type
                $mimeType = $request->file('media')->getMimeType();
                if (str_contains($mimeType, 'image')) {
                    $messageType = 'image';
                } elseif (str_contains($mimeType, 'video')) {
                    $messageType = 'video';
                }
            }
    
            $message = GroupMessage::create([
                'sender_id' => Auth::id(),
                // 'sender_id' => 1,
                // 'receiver_id' => $request->input('receiver_id'),
                'receiver_group_id' => $request->input('group_id'),
                'content' => $string,
                'media_url' => $mediaUrl,
                'message_type' => $messageType,
            ]);

            // Prepare the data you want to broadcast
            $messageData = [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_group_id' => $request->input('group_id'),
                'content' => $message->content,
                'media_url' => $message->media_url,
                'message_type' => $message->message_type,  // Include the message type
                'timestamp' => $message->created_at->toDateTimeString(),
            ];

            // Dispatch the event
            $pusher_channel = $receiving_group->group_pusher_channel;
            event(new GroupMessageSent($messageData, $pusher_channel));
    
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'data' => $message,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            $mediaErrors = $e->errors()['media'] ?? null;
            if ($mediaErrors) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media validation failed.',
                    'errors' => $mediaErrors,
                ], 422);
            }
    
            // Handle other validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            // Return a generic error response

            dd($th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/group/messages/get/{group_id}",
     *     @OA\Get(
     *         summary="Retrieve all messages between the authenticated user and a specified group",
     *         tags={"Group Message"},
     *         @OA\Parameter(
     *             name="group_id",
     *             in="path",
     *             required=true,
     *             description="The ID of the group to retrieve messages with",
     *             @OA\Schema(type="integer", example=2)
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=404, description="User not found"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function getGroupMessages($group_id){
        try {
            $group = GroupChat::findOrFail($group_id);
    
            $authUserId = Auth::id();
            $targetGroupId = $group_id;
    
            $messages = GroupMessage::where(function ($query) use ($authUserId, $targetGroupId) {
                $query->where('sender_id', $authUserId)
                      ->where('receiver_group_id', $targetGroupId);
            })
            ->orWhere(function ($query) use ($authUserId, $targetGroupId) {
                $query->where('sender_id', $targetGroupId)
                      ->where('receiver_group_id', $authUserId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    
            return response()->json([
                'success' => true,
                'data' => $messages,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // User not found
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
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
     *     path="/group/messages/delete",
     *     @OA\Delete(
     *         summary="Delete a group message",
     *         tags={"Group Message"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message_id", type="integer", example=1, description="The ID of the message to be deleted")
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function deleteGroupMessage(Request $request){
        try {
            $request->validate([
                'message_id' => 'required|exists:group_chat_messages,id',
            ]);
    
            $messageId = $request->input('message_id');
            $message = GroupMessage::findOrFail($messageId);
    
            $authUserId = Auth::id();
            // $authUserId = 1;
    
            // Check if the authenticated user is the sender
            if ($message->sender_id !== $authUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            // Check if the message has media and delete the file from the server
            if ($message->media_url) {
                // Extract the file path from the URL
                $mediaPath = str_replace(asset('storage/'), '', $message->media_url);
                
                // Delete the media file from the public/messages folder
                if (Storage::disk('public')->exists($mediaPath)) {
                    Storage::disk('public')->delete($mediaPath);
                }
            }
    
            // Delete the message
            $message->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully.',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/group/messages/update",
     *     summary="Update a message",
     *     tags={"Group Message"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message_id", type="integer", example=1, description="The ID of the message to be updated"),
     *             @OA\Property(property="content", type="string", example="Updated message content", description="The new content of the message")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=403, description="Unauthorized action"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Server failure")
     * )
     */
    public function updateGroupMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:group_chat_messages,id',
                'content' => 'required|string|max:255',
            ]);
    
            $messageId = $request->input('message_id');
            $content = $request->input('content');
            $message = GroupMessage::findOrFail($messageId);
    
            $authUserId = Auth::id();
    
            // Check if the authenticated user is the sender
            if ($message->sender_id !== $authUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
    
            // Update the message content
            $message->content = $content;
            $message->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Message updated successfully.',
                'data' => $message,
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
}
