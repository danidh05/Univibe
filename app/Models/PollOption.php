<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'option', 'votes'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
