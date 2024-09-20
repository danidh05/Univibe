<?php

namespace App\Http\Controllers;

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
                'receiver_id' => $request->input('receiver_id'),
                'content' => $string,
                'media_url' => $mediaUrl,
                'message_type' => $messageType,
            ]);

            // Prepare the data you want to broadcast
            $messageData = [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content,
                'media_url' => $message->media_url,
                'message_type' => $message->message_type,  // Include the message type
                'timestamp' => $message->created_at->toDateTimeString(),
            ];

            // Dispatch the event
            $pusher_channel = $receiving_user->pusher_channel;
            event(new PrivateMessageSent($messageData, $pusher_channel));
    
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'data' => $message,
            ], 201);
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
            ]);
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

    public function deletePrivateMessage(Request $request){
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
            ]);
    
            $messageId = $request->input('message_id');
            $message = Message::findOrFail($messageId);
    
            // $authUserId = Auth::id();
            $authUserId = 1;
    
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
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

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
