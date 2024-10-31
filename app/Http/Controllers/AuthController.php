<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication operations"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "password", "university_id", "major_id"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@students.university.edu"),
     *             @OA\Property(property="password", type="string", example="Password@123"),
     *             @OA\Property(property="profile_picture", type="string", format="binary", example="profile_picture.jpg"),
     *             @OA\Property(property="is_verified", type="boolean", example=false),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="university_id", type="integer", example=1),
     *             @OA\Property(property="major_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email was sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification Email was sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $formFields = $request->validate(
            [
                'username' => ['required', 'min:3'],

                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email'),
                    'regex:/^[0-9][a-zA-Z0-9._%+-]*@students\.[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                "is_verified" => "boolean",
                "is_active" => "nullable|boolean",
                "university_id" => "integer|required",
                "major_id" => "integer|required",
            ],
            [
                'email.regex' => 'The email must be a student email.',
            ]
        );

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $formFields['profile_picture'] = $profilePicturePath;
        } else {
            $formFields['profile_picture'] = null;
        }

        $formFields['password'] = bcrypt($formFields['password']);

        $user = User::create($formFields);
        $user->pusher_channel = 'user-' . $user->id;
        $user->save();

        event(new Registered($user));

        return response()->json([
            'message' => 'Verification Email was sent',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john.doe@students.university.edu"),
     *             @OA\Property(property="password", type="string", example="Password@123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="your_jwt_token_here"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@students.university.edu"),
     *                 @OA\Property(property="role", type="object",
     *                     @OA\Property(property="Role_name", type="string", example="Student")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $userData = $user->toArray();
            $userData['role'] = ['Role_name' => $user->role ? $user->role->Role_name : null];

            $token = $user->createToken('main')->plainTextToken;
            if ($user->is_verified) {
                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $userData,
                ]);
            } else {
                event(new Registered($user));

                return response()->json([
                    'message' => 'Please verify email to login',
                ]);
            }
        }

        return response()->json([
            'error' => 'Invalid credentials',
        ], 401);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout a user",
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        Auth::logout(); // Log the user out

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
