<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * @OA\PathItem(
     *     path="/user/update",
     *     @OA\Post(
     *         summary="Update user profile",
     *         tags={"User Profile"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"username"},
     *                 @OA\Property(property="username", type="string", example="new_username"),
     *                 @OA\Property(property="bio", type="string", example="This is my new bio."),
     *                 @OA\Property(property="profile_picture", type="string", format="binary")
     *             )
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            // Validate the input
            $validated = $request->validate([
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validate image file
                'bio' => 'nullable|string|max:500',
                'username' => 'nullable|string|max:255|unique:users,username,' . Auth::id(), // Ensure unique username
            ]);

            // Get the current authenticated user
            $user = User::FindOrFail(Auth::id());

            // Track if any changes are made
            $changesMade = false;

            // Handle profile_picture if uploaded
            if ($request->hasFile('profile_picture')) {
                // Delete the old profile picture if it exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store the new profile picture and save its path
                $filePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $filePath;
                $changesMade = true;
            }

            // Update bio if it's different
            if ($request->filled('bio')) {
                $string = app('profanityFilter')->filter($request->input('bio'));
                if($string !== $user->bio){
                    $user->bio = $string;
                    $changesMade = true;
                }
            }

            // Update username if it's different
            if ($request->filled('username') && $request->username !== $user->username) {
                $user->username = $request->username;
                $changesMade = true;
            }

            // If no changes were made, return an error
            if (!$changesMade) {
                return response()->json(['error' => 'No changes detected.'], 400);
            }

            // Save changes to the database
            $user->save();

            // Return success message
            return response()->json([
                'message' => 'Profile updated successfully.'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
