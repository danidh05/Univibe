<?php

namespace App\Models;

use App\Models\Like;
use App\Models\User;
use App\Models\Share;
use App\Models\Comment;
use App\Models\PollOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'media_url',
        'postType',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function pollOptions()
    {
        return $this->hasMany(PollOption::class);
    }

    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    public function reposts()
    {
    return $this->hasMany(Repost::class);
    }

}
