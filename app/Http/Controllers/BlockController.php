<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    // Block a user
    public function blockUser(Request $request)
    {
        $request->validate([
            'blocked_user_id' => 'required|exists:users,id',
        ]);

        $blockerUserId = Auth::id();
        $blockedUserId = $request->input('blocked_user_id');

        $blockExists = Block::where('blocker_user_id', $blockerUserId)
            ->where('blocked_user_id', $blockedUserId)
            ->exists();

        if ($blockExists) {
            return response()->json([
                'message' => 'User is already blocked.'
            ], 400);
        }

        $block = Block::create([
            'blocker_user_id' => $blockerUserId,
            'blocked_user_id' => $blockedUserId,
        ]);

        return response()->json([
            'message' => 'User has been successfully blocked.',
            'block' => $block,
        ], 201);
    }

    public function getBlockedAccounts()
    {
        $blockerUserId = Auth::id();

        $blockedUsers = Block::where('blocker_user_id', $blockerUserId)
            ->with('blockedUser')
            ->get()
            ->map(function ($block) {
                return $block->blockedUser; // Return only the blocked user information
            });

        return response()->json([
            'blocked_users' => $blockedUsers,
        ], 200);
    }
}
