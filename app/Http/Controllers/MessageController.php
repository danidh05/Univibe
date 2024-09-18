<?php

namespace App\Http\Controllers;

use App\Events\PrivateMessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;
use App\Events\YourEventName;
use App\Models\User;

class MessageController extends Controller
{
    public function sendPrivateMessage(Request $request){
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'required|string|max:255',
            ]);

            $receiving_user = User::find($request->input('receiver_id'));
            $string = app('profanityFilter')->filter($request->input('content'));
    
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->input('receiver_id'),
                'content' => $string,
            ]);

            // Prepare the data you want to broadcast
            $messageData = [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content,
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
    
            $authUserId = Auth::id();
    
            // Check if the authenticated user is the sender
            if ($message->sender_id !== $authUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
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
