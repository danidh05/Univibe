<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Adminmiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No user is authenticated.'], 401);
        }
        if ($user->role->role_name === 'admin') {
            return $next($request);
        }
        return response()->json(['message' => 'Unauthorized: Only admin users can access this resource.'], 403);
    }
}
