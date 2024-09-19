<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getAdminInfo()
    {
        return response()->json([
            'message' => 'Welcome, Admin! .',
            'data' => [
                'admin' => 'Admin details .',
            ]
        ]);
    }
    public function getUserInfo()
    {
        return response()->json([
            'message' => 'Welcome, User! Here is your info.',
            'data' => [
                'admin' => 'Admin details.',
            ]
        ]);
    }
    public function deactivate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->is_deactivated = 1;
        $user->save();

        return response()->json([
            'message' => 'Account has been successfully deactivated.',
        ], 200);
    }
    public function undeactivate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->is_deactivated = 0;
        $user->save();

        return response()->json([
            'message' => 'Account has been successfully Undeactivated.',
        ], 200);
    }
}
