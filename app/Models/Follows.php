<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follows extends Model
{
    use HasFactory;

    // Define which attributes are mass assignable
    protected $fillable = [
        'follower_id',
        'followed_id',
        'is_friend',
    ];

    /**
     * Define the relationship between Follow and User.
     * The follower is a user who is following someone.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Define the relationship between Follow and User.
     * The followed is the user who is being followed.
     */
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }

    /**
     * Check if the follow relationship is a friendship (i.e., is_friend is true).
     */
    public function isFriend()
    {
        return $this->is_friend;
    }
}
