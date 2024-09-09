<?php

namespace App\Http\Controllers;

use App\Events\PrivateMessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;
use App\Events\YourEventName;

class MessageController extends Controller
{
    public function sendPrivateMessage(Request $request){
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'required|string|max:255',
            ]);
    
            $message = Message::create([
                // 'sender_id' => Auth::id(),
                'sender_id' => 1, // Testing Purposes
                'receiver_id' => $request->input('receiver_id'),
                'content' => $request->input('content'),
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
            event(new PrivateMessageSent($messageData));
    
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'data' => $message,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getPrivateMessages(Request $request){
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
    
            // $authUserId = Auth::id();
            $authUserId = 2; // Testing Purposes
            $targetUserId = $request->input('user_id');
    
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
        } catch (\Throwable $th) {
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
            $authUserId = 1; // Testing Purposes
    
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
    
            // $authUserId = Auth::id();
            $authUserId = 1; // Testing Purposes
    
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
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
