<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'reason',
        'Descreption_reason',
    ];
    public function reporterUser()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}
