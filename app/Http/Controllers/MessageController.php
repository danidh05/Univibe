<?php

namespace App\Http\Controllers;

use App\Events\AllMessagesRead;
use App\Events\MessageDelivered;
use App\Events\PrivateMessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;
use App\Events\YourEventName;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * @OA\PathItem(
     *     path="/messages/send",
     *     @OA\Post(
     *         summary="Send a private message",
     *         tags={"Private Message"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="receiver_id", type="integer", example=2, description="The ID of the user receiving the message"),
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
    public function sendPrivateMessage(Request $request){
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'required|string|max:255',
                'media' => 'nullable|mimes:jpeg,png,jpg,mp4,mov,avi|max:20480',
            ]);

            $receiving_user = User::find($request->input('receiver_id'));
            $string = app('profanityFilter')->filter($request->input('content'));

            // Handle media upload
            $mediaUrl = null;
            $messageType = 'text';

            if ($request->hasFile('media')) {
                // Store the media in the public/messages folder
                $mediaPath = $request->file('media')->store('messages', 'public');
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
    
            $message = Message::create([
                'sender_id' => Auth::id(),
                // 'sender_id' => 1,
                'receiver_id' => $request->input('receiver_id'),
                'content' => $string,
                'media_url' => $mediaUrl,
                'message_type' => $messageType,
                'is_read' => false,
                'is_delivered' => false,
            ]);

            // Prepare the data you want to broadcast
            $messageData = [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content,
                'media_url' => $message->media_url,
                'message_type' => $message->message_type,  // Include the message type
                'is_read' => $message->is_read,
                'is_delivered' => $message->is_delivered,
                'timestamp' => $message->created_at->toDateTimeString(),
            ];

            // Dispatch the event
            $pusher_channel = $receiving_user->pusher_channel;
            event(new PrivateMessageSent($messageData, $pusher_channel));
    
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
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/messages/read_all_private_messages",
     *     @OA\Post(
     *         summary="Mark all the messages between you and another user as read",
     *         tags={"Private Message"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=2, description="The ID of the user whose messages are to be marked as read")
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=404, description="No messages found to update"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function readAllPrivateMessages(Request $request){
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            // Fetch and update the messages where sender_id is the user_id
            // and receiver_id is the authenticated user's ID
            $updatedMessages = Message::where('sender_id', $request->user_id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'is_delivered' => true,
            ]);

            $user = User::find($request->user_id);

            $pusher_data = [
                'receiver_id' => Auth::id(),
                'read_all_messages' => true,
            ];

            // Dispatch the event
            $pusher_channel = $user->pusher_channel;
            event(new AllMessagesRead($pusher_data, $pusher_channel));

            // Check if any messages were updated
            if ($updatedMessages > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Messages updated successfully.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No messages found to update.',
                ], 404);
            }
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
     *     path="/messages/mark_message_delivered",
     *     @OA\Post(
     *         summary="Mark a message of yours as delivered",
     *         tags={"Private Message"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message_id", type="integer", example=1, description="The ID of the message to be marked as delivered")
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function markAsDelivered(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
            ]);
    
            $message = Message::find($request->input('message_id'));
            
            // Ensure that the receiver is the one marking the message as delivered
            if ($message->receiver_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
    
            $message->is_delivered = true;
            $message->save();
    
            $pusher_data = [
                'updated_message' => $message,
            ];
    
            // Dispatch the event
            $pusher_channel = $message->sender->pusher_channel;
            event(new MessageDelivered($pusher_data, $pusher_channel));
    
            return response()->json([
                'success' => true,
                'message' => 'Message marked as delivered.',
                'data' => $message,
            ], 200);
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
     *     path="/messages/get/{user_id}",
     *     @OA\Get(
     *         summary="Retrieve all messages between the authenticated user and a specified user",
     *         tags={"Private Message"},
     *         @OA\Parameter(
     *             name="user_id",
     *             in="path",
     *             required=true,
     *             description="The ID of the user to retrieve messages with",
     *             @OA\Schema(type="integer", example=2)
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=404, description="User not found"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function getPrivateMessages($user_id){
        try {
            $user = User::findOrFail($user_id);
    
            $authUserId = Auth::id();
            $targetUserId = $user_id;
    
            $messages = Message::where(function ($query) use ($authUserId, $targetUserId) {
                $query->where('sender_id', $authUserId)
                      ->where('receiver_id', $targetUserId);
            })
            ->orWhere(function ($query) use ($authUserId, $targetUserId) {
                $query->where('sender_id', $targetUserId)
                      ->where('receiver_id', $authUserId);
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
                'message' => 'User not found.',
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
     *     path="/messages/delete",
     *     @OA\Delete(
     *         summary="Delete a private message",
     *         tags={"Private Message"},
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
    public function deletePrivateMessage(Request $request){
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
            ]);
    
            $messageId = $request->input('message_id');
            $message = Message::findOrFail($messageId);
    
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
     *     path="/messages/update",
     *     summary="Update a message",
     *     tags={"Private Message"},
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
    public function updatePrivateMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
                'content' => 'required|string|max:255',
            ]);
    
            $messageId = $request->input('message_id');
            $content = $request->input('content');
            $message = Message::findOrFail($messageId);
    
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
