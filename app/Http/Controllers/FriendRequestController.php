<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use App\Models\Follows;
use App\Models\FriendRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendRequestController extends Controller
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
     *     path="/user/send_friend_request",
     *     @OA\Post(
     *         summary="Send a friend request to a user",
     *         tags={"Friend"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=400, description="Other errors/conflicts, already friend, already sent friend request, pending friend request from said user, sending friend request to yourself"),
     *         @OA\Response(response=404, description="User not found"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"user_id"},
     *                 @OA\Property(property="user_id", type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */
    public function send_friend_request(Request $request){
        try {
            $user_id = $request->input('user_id');
            $user = User::findOrFail($user_id);

            $authenticated_user_id = Auth::id(); // Get the ID of the authenticated user
            $authenticated_user = User::find(Auth::id());

            // Check if the user is trying to send a request to themselves
            if ($user_id == $authenticated_user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot send a friend request to yourself.',
                ], 400);
            }

            // Check if there's already a friend request
            $already_sent_friend_request = FriendRequest::where('from_id', $authenticated_user_id)->where('to_id', $user_id)->exists();

            // Check if there's already a friend request sent to me from that user
            $already_received_friend_request = FriendRequest::where('from_id', $user_id)->where('to_id', $authenticated_user_id)->exists();

            if ($already_sent_friend_request){
                return response()->json([
                    'success' => false,
                    'message' => 'You have already sent a friend request to this user.',
                ], 400);
            }

            if ($already_received_friend_request){
                return response()->json([
                    'success' => false,
                    'message' => 'You have a pending friend request from this user.',
                ], 400);
            }

            // Check if they are already friends
            $already_friends = Follows::where(function ($query) use ($authenticated_user_id, $user_id) {
                $query->where('follower_id', $authenticated_user_id)
                    ->where('followed_id', $user_id)
                    ->where('is_friend', true);
            })->orWhere(function ($query) use ($authenticated_user_id, $user_id) {
                $query->where('follower_id', $user_id)
                    ->where('followed_id', $authenticated_user_id)
                    ->where('is_friend', true);
            })
            ->exists();

            if ($already_friends) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already friends with this user.',
                ], 400);
            }
            
            $friend_request = FriendRequest::create([
                'from_id' => $authenticated_user_id,
                'to_id' => $user_id,
            ]);

            $pusher_channel = $user->pusher_channel;

            $this->notificationService->createNotification(
                $user_id,
                'friend_request',
                $authenticated_user->username . ' sent you a friend request',
                $friend_request,
                $pusher_channel
            );

            return response()->json([
                'success' => true,
                'message' => 'Friend request succesfully sent.',
                'friend_request' => $friend_request,
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
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/accept_friend_request",
     *     @OA\Post(
     *         summary="Accept a friend request",
     *         tags={"Friend"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"request_id"},
     *                 @OA\Property(property="request_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function accept_friend_request(Request $request){
        try {
            $request->validate([
                'request_id' => 'required|exists:friend_requests,id',
            ]);

            $friend_request = FriendRequest::where('id', $request->input('request_id'))->first();

            if($this->verify_friend_request_authorization($friend_request->to_id)){
                $from_id = $friend_request->from_id;
                $to_id = $friend_request->to_id;

                // check if they already follow each others
                $sender_follows_receiver = Follows::where('follower_id', $from_id)->where('followed_id', $to_id)->first();
                $receiver_follows_sender = Follows::where('follower_id', $to_id)->where('followed_id', $from_id)->first();

                if ($sender_follows_receiver) {
                    // They already follow each other, so update is_friends to true for both
                    Follows::where('follower_id', $from_id)
                        ->where('followed_id', $to_id)
                        ->update(['is_friend' => true]);
                }else{
                    $friendship1 = Follows::create([
                        'follower_id' => $from_id,
                        'followed_id' => $to_id,
                        'is_friend' => true,
                    ]);
                }

                if($receiver_follows_sender){
                    Follows::where('follower_id', $to_id)
                    ->where('followed_id', $from_id)
                    ->update(['is_friend' => true]);
                }else{
                    $friendship2 = Follows::create([
                        'follower_id' => $to_id,
                        'followed_id' => $from_id,
                        'is_friend' => true,
                    ]);
                }

                $friend_request->delete();

                $this->notificationService->createNotification(
                    $from_id,
                    'friend_accepted',
                    $friend_request->receiver->username . ' accepted your friend request',
                    null,
                    $friend_request->sender->pusher_channel
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Friend request succesfully accepted.',
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to accept this friend request.',
                ], 403);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom validation error response
            return response()->json([
                'success' => false,
                'message' => 'The selected request id is invalid.',
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

    /**
     * @OA\PathItem(
     *     path="/user/reject_friend_request",
     *     @OA\Post(
     *         summary="Reject a friend request",
     *         tags={"Friend"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure"),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"request_id"},
     *                 @OA\Property(property="request_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function reject_friend_request(Request $request){
        try {
            $request->validate([
                'request_id' => 'required|exists:friend_requests,id',
            ]);

            $friend_request = FriendRequest::where('id', $request->input('request_id'))->first();

            if($this->verify_friend_request_authorization($friend_request->to_id)){
                $friend_request->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Friend request successfully rejected.',
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to reject this friend request.',
                ], 403);
            }
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

    private function verify_friend_request_authorization($to_id){
        return $to_id == Auth::id();
    }

    /**
     * @OA\PathItem(
     *     path="/user/cancel_friend_request",
     *     @OA\Post(
     *         operationId="cancelFriendRequest",
     *         summary="Cancel a friend request",
     *         tags={"Friend"},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\JsonContent(
     *                 required={"request_id"},
     *                 @OA\Property(property="request_id", type="integer", example=1)
     *             )
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=403, description="Unauthorized action"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function cancel_friend_request(Request $request){
        try {
            $request->validate([
                'request_id' => 'required|exists:friend_requests,id',
            ]);

            $friend_request = FriendRequest::where('id', $request->input('request_id'))->first();

            if($this->verify_friend_request_authorization($friend_request->from_id)){
                $friend_request->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Friend request successfully canceled.',
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to cancel this friend request.',
                ], 403);
            }
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

    /**
     * @OA\PathItem(
     *     path="/user/remove_friend",
     *     @OA\Delete(
     *         summary="Remove a friend",
     *         tags={"Friend"},
     *         @OA\Parameter(
     *             name="user_id",
     *             in="query",
     *             required=true,
     *             description="ID of the user to unfriend",
     *             @OA\Schema(type="integer")
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=400, description="Other errors/conflicts, unfriending yourself, removing a user that isn't a friend"),
     *         @OA\Response(response=422, description="Validation failed"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function remove_friend(Request $request){
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            if(Auth::id()==$request->input('user_id')){
                return response()->json([
                    'success' => false,
                    'message' => 'You can\'t unfriend yourself.'
                ], 400);
            }

            $from_id = Auth::id();
            $to_id = $request->input('user_id');

            $sender_follows_receiver = Follows::where('follower_id', $from_id)->where('followed_id', $to_id)->where('is_friend', true)->first();
            $receiver_follows_sender = Follows::where('follower_id', $to_id)->where('followed_id', $from_id)->where('is_friend', true)->first();

            if(!$sender_follows_receiver||!$receiver_follows_sender){
                return response()->json([
                    'success' => false,
                    'message' => 'You two aren\'t friends.'
                ], 400);
            }

            $sender_follows_receiver->delete();
            $receiver_follows_sender->delete();

            return response()->json([
                'success' => true,
                'message' => 'Friend successfully removed.',
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

    /**
     * @OA\PathItem(
     *     path="/user/get_friends_list",
     *     @OA\Get(
     *         summary="Get friends list",
     *         tags={"Friend"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function get_friends_list(){
        try {
            $user = User::findOrFail(Auth::id());
            // Get the IDs of the friends from the Follows table
            $friendIds = Follows::where('follower_id', $user->id)
                ->where('is_friend', true)
                ->pluck('followed_id');

            // Retrieve friend details from the User table
            $friends_list = User::whereIn('id', $friendIds)
                ->select('id', 'username', 'email', 'profile_picture', 'is_active')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Friend list successfully retrieved.',
                'friends_list' => $friends_list
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
     *     path="/user/get_all_sent_friend_requests",
     *     @OA\Get(
     *         summary="Get all sent friend requests",
     *         tags={"Friend"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function get_all_sent_friend_requests(){
        try {
            $user = User::findOrFail(Auth::id());
            $sent_friend_requests = $user->sentFriendRequests;

            return response()->json([
                'success' => true,
                'message' => 'Sent friend requests successfully retrieved.',
                'sent_friend_requests' => $sent_friend_requests
            ], 200);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\PathItem(
     *     path="/user/get_all_received_friend_requests",
     *     @OA\Get(
     *         summary="Get all received friend requests",
     *         tags={"Friend"},
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function get_all_received_friend_requests(){
        try {
            $user = User::findOrFail(Auth::id());
            $received_friend_requests = $user->receivedFriendRequests;

            return response()->json([
                'success' => true,
                'message' => 'Received friend requests succesfully retreived.',
                'received_friend_requests' => $received_friend_requests
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
     *     path="/user/is_friend/{user_id}",
     *     @OA\Get(
     *         summary="Checks if you are friends with the specified user",
     *         tags={"Friend"},
     *         @OA\Parameter(
     *             name="user_id",
     *             in="path",
     *             required=true,
     *             description="The ID of the user to check friendship status",
     *             @OA\Schema(type="integer")
     *         ),
     *         @OA\Response(response=200, description="Successful operation"),
     *         @OA\Response(response=404, description="User not found"),
     *         @OA\Response(response=500, description="Server failure")
     *     )
     * )
     */
    public function is_friend($user_id){
        try {
            $user = User::findOrFail(Auth::id());
            $is_friends = $user->isFriend($user_id);

            return response()->json([
                'success' => true,
                'message' => $is_friends ? 'You are friends with this user.' : 'You are not friends with this user.',
                'is_friend' => $is_friends,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the specific case where the user is not found
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (\Throwable $th) {
            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}