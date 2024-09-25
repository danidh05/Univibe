<?php

namespace App\Http\Middleware;

use App\Models\GroupChat;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckGroupMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // first check if request has group id otherwise don't bother reaching the controller
        try {
            $request->validate([
                'group_chat_id' => 'required|exists:group_chats,id', // also checks if the group exists
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
        $groupChat = GroupChat::find($request->input('group_chat_id'));
        // Check if the user is a member of the group
        $isMember = $groupChat->members()->where('user_id', Auth::id())->exists();

        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this group.'
            ], 404);
        }

        return $next($request);
    }
}
