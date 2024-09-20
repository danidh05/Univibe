<?php

namespace App\Models;

use App\Models\Share;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_picture',
        'bio',
        'role_id',
        'is_active',
        'is_verified',
        'major_id',
        'university_id',
        "is_deactivated",
        'pusher_channel',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function shares()
    {
        return $this->hasMany(Share::class, 'user_id');
    }

    // This user receives shared posts
    public function receivedShares()
    {
        return $this->hasMany(Share::class, 'recipient_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function followers()
    {
        return $this->hasMany(Follows::class, 'followed_id');
    }

    public function following()
    {
        return $this->hasMany(Follows::class, 'follower_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function ownedGroups()
    {
        return $this->hasMany(GroupChat::class, 'owner_id');
    }

    public function groups()
    {
        return $this->belongsToMany(GroupChat::class, 'group_members');
    }

    public function friends()
    {
        return $this->hasMany(Follows::class, 'follower_id')
            ->where('is_friend', true);
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'from_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'to_id');
    }

    public function isFollowing($userId)
    {
        return $this->followings()->where('followed_id', $userId)->exists();
    }

    // Check if a user is followed by another user
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    // Check if two users are friends (mutual follows and is_friend = true)
    public function isFriend($userId)
    {
        // Check mutual follows
        $following = $this->following()->where('followed_id', $userId)->where('is_friend', true)->exists();
        $followed = $this->followers()->where('follower_id', $userId)->where('is_friend', true)->exists();

        return $following && $followed;
    }
}