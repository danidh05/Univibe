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

    public function get_follower_list(){
        try {
            $user = User::findOrFail(Auth::id());

            // Get the list of followers
            $follows = $user->followers()->with(['follower' => function ($query) {
                $query->select('id', 'username', 'email', 'profile_picture', 'is_active'); // Only select public fields
            }])->get();

            // Extract the follower details
            $followers_list = $follows->map(function ($follow) {
                return $follow->follower;
            });

            // $followers_list = Auth::user()->followers();
            return response()->json([
                'success' => true,
                'message' => 'Followers list retreived succesfully.',
                'followers_list' => $followers_list,
            ], 200);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function get_following_list(){
        try {
            $user = User::findOrFail(Auth::id());

            // Get the list of followers
            $follows = $user->following()->with(['followed' => function ($query) {
                $query->select('id', 'username', 'email', 'profile_picture', 'is_active'); // Only select public fields
            }])->get();

            // Extract the follower details
            $following_list = $follows->map(function ($follow) {
                return $follow->followed; // Assuming the 'follower' relation in Follows model points to the User model
            });

            // $followers_list = Auth::user()->followers();
            return response()->json([
                'success' => true,
                'message' => 'Following list retreived succesfully.',
                'following_list' => $following_list,
            ], 200);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
