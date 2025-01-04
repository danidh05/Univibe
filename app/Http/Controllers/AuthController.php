<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
     *             required={"username", "email", "password", "password_confirmation", "university_id", "major_id"},
     *             @OA\Property(property="username", type="string", example="john_doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@students.university.edu"),
     *             @OA\Property(property="password", type="string", example="Password@123"),
     *             @OA\Property(property="password_confirmation", type="string", example="Password@123"),
     *             @OA\Property(property="profile_picture", type="string", format="binary", example="profile_picture.jpg"),
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Email already taken",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The email has already been taken")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
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
                    ->symbols(),
            ],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'university_id' => 'required|integer|exists:universities,id',
            'major_id' => 'required|integer|exists:majors,id',
        ], [
            'email.regex' => 'The email must be a student email.',
            'university_id.exists' => 'The selected university is invalid.',
            'major_id.exists' => 'The selected major is invalid.',
        ]);
    
        // Check for validation errors
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(), // Return all validation errors
            ], 400);
        }
    
        $formFields = $validator->validated();
    
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $formFields['profile_picture'] = $profilePicturePath;
        } else {
            $formFields['profile_picture'] = null;
        }
    
        // Hash password
        $formFields['password'] = bcrypt($formFields['password']);
    
        // Create user
        $user = User::create($formFields);
        $user->pusher_channel = 'user-' . $user->id;
        $user->save();
    
        // Fire Registered event to send verification email
        event(new Registered($user));
    
        return response()->json([
            'message' => 'Verification Email was sent',
        ], 201); // Use 201 Created for successful resource creation
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
     *                 @OA\Property(property="username", type="string", example="john_doe"),
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
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Please verify your email to login")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        // Validate input data
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate user
        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Check if user is verified
            if (!$user->is_verified) {
                event(new Registered($user));
                return response()->json([
                    'message' => 'Please verify your email to login',
                ], 403);
            }

            // Generate Sanctum token
            $token = $user->createToken('main')->plainTextToken;

            // Prepare user data for response
            $userData = $user->toArray();
            $userData['role'] = ['Role_name' => $user->role ? $user->role->Role_name : null];

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $userData,
            ]);
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
        // Revoke the Sanctum token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     tags={"Authentication"},
     *     summary="Reset user password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "password_confirmation", "token"},
     *             @OA\Property(property="email", type="string", example="john.doe@students.university.edu"),
     *             @OA\Property(property="password", type="string", example="NewPassword@123"),
     *             @OA\Property(property="password_confirmation", type="string", example="NewPassword@123"),
     *             @OA\Property(property="token", type="string", example="your_reset_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid token")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'token' => 'required|string',
        ], [
            'email.exists' => 'The selected email is invalid.',
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Reset password logic (replace with your actual implementation)
        // Example: Verify token and update password
        $user = User::where('email', $request->email)->first();
        if (!$user || $request->token !== 'valid_token') {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password reset successful'], 200);
    }
}