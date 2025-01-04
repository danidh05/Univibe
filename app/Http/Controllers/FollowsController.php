<?php

namespace App\Http\Controllers;

use App\Models\Follows;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowsController extends Controller
{
    protected $notificationService;

    /**
     * Inject NotificationService into the controller.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    /**
     * @OA\PathItem(
     *     path="/user/follow",
     *     @OA\Post(
     *         summary="Follow a user",
     *         tags={"Follow"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 @OA\Property(property="user_id", type="integer", example=1)
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function follow(Request $request){
        try {
            $user_id = $request->input('user_id');
            $follow = Follows::create([
                'follower_id' => Auth::id(),
                'followed_id' => $user_id,
                'is_friend' => false,
            ]);

            $this->notificationService->createNotification(
                $user_id,
                'follow',
                $follow->follower->username . ' started following you',
                $follow,
                $follow->followed->pusher_channel
            );

            return response()->json([
                'success' => true,
                'message' => 'User succesfully followed.',
                'follow' => $follow
            ], 200);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/unfollow",
     *     @OA\Post(
     *         summary="Unfollow a user",
     *         tags={"Follow"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 @OA\Property(property="user_id", type="integer", example=1)
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=404, description="Not found, You are not following that user"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function unfollow(Request $request){
        try {
            $user_id = $request->input('user_id');
            $follow = Follows::where('follower_id', Auth::id())->where('followed_id', $user_id)->first();

            // Check if the follow relationship exists
            if (!$follow) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not following this user.',
                ], 404);
            }

            $follow->delete();

            return response()->json([
                'success' => true,
                'message' => 'User succesfully unfollowed.',
            ], 200);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/is_following/{user_id}",
     *     @OA\Get(
     *         summary="Check if you are following the specified user",
     *         tags={"Follow"},
     *         @OA\Parameter(
     *             name="user_id",
     *             in="path",
     *             required=true,
     *             @OA\Schema(type="integer")
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=404, description="User not found"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function is_following($user_id){
        try {
            $user = User::findOrFail($user_id);
            // Check if the authenticated user is following the provided user
            $isFollowing = Follows::where('follower_id', Auth::id())
                                ->where('followed_id', $user_id)
                                ->exists();

            // Return a response based on whether they are following or not
            return response()->json([
                'success' => true,
                'message' => $isFollowing ? 'You are following this user.' : 'You are not following this user.',
                'is_following' => $isFollowing,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // User not found
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/is_followed/{user_id}",
     *     @OA\Get(
     *         summary="Check if you are followed by the specified user",
     *         tags={"Follow"},
     *         @OA\Parameter(
     *             name="user_id",
     *             in="path",
     *             required=true,
     *             @OA\Schema(type="integer")
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=404, description="User not found"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function is_followed($user_id){
        try {
            $user = User::findOrFail($user_id);
            // Check if the authenticated user is followed by the provided user
            $isFollowing = Follows::where('followed_id', Auth::id())
                                ->where('follower_id', $user_id)
                                ->exists();

            // Return a response based on whether they are following or not
            return response()->json([
                'success' => true,
                'message' => $isFollowing ? 'You are followed by this user.' : 'You are not followed by this user.',
                'is_following' => $isFollowing,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // User not found
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/get_follower_list",
     *     @OA\Get(
     *         summary="Gets you a list of the users that follow you",
     *         tags={"Follow"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function get_follower_list(){
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }
    
            // Attempt to find the authenticated user
            $user = User::findOrFail(Auth::id());
    
            // Get the list of followers
            $follows = $user->followers()->with(['follower' => function ($query) {
                $query->select('id', 'username', 'email', 'profile_picture', 'is_active'); // Only select public fields
            }])->get();
    
            // Extract the follower details
            $followers_list = $follows->map(function ($follow) {
                return $follow->follower;
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Followers list retrieved successfully.',
                'followers_list' => $followers_list,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // User not found
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/get_following_list",
     *     @OA\Get(
     *         summary="Gets you a list of the users you follow",
     *         tags={"Follow"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function get_following_list(){
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }
    
            // Attempt to find the authenticated user
            $user = User::findOrFail(Auth::id());
    
            // Get the list of followers
            $follows = $user->following()->with(['followed' => function ($query) {
                $query->select('id', 'username', 'email', 'profile_picture', 'is_active'); // Only select public fields
            }])->get();
    
            // Extract the follower details
            $following_list = $follows->map(function ($follow) {
                return $follow->followed; // Assuming the 'follower' relation in Follows model points to the User model
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Following list retrieved successfully.',
                'following_list' => $following_list,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // User not found
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }
}