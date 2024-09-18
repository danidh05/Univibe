<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
