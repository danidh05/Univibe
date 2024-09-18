<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserIdValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if 'user_id' is present in the request
        if (!$request->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'The user_id parameter is required.',
            ], 422);
        }

        // Validate that 'user_id' is a valid integer
        $userId = $request->input('user_id');
        if (!is_numeric($userId) || intval($userId) <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'The user_id must be a positive integer.',
            ], 422);
        }

        // Check if the user exists in the database
        $userExists = DB::table('users')->where('id', $userId)->exists();
        if (!$userExists) {
            return response()->json([
                'success' => false,
                'message' => 'The user with the provided user_id does not exist.',
            ], 404);
        }

        // Proceed with the request if 'user_id' is valid and exists
        return $next($request);
    }
}
