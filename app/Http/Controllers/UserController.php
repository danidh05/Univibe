<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User operations"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user/deactivate",
     *     tags={"User"},
     *     summary="Deactivate a user's account",
     *     @OA\Response(
     *         response=200,
     *         description="Account has been successfully deactivated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Account has been successfully deactivated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/user/undeactivate",
     *     tags={"User"},
     *     summary="Undeactivate a user's account",
     *     @OA\Response(
     *         response=200,
     *         description="Account has been successfully undeactivated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Account has been successfully undeactivated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function undeactivate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->is_deactivated = 0;
        $user->save();

        return response()->json([
            'message' => 'Account has been successfully undeactivated.',
        ], 200);
    }
}
