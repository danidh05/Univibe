<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Share extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'recipient_id', 'share_type'];

    // The user who shared the post
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The user receiving the shared post (if applicable)
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    // The post that is being shared
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
