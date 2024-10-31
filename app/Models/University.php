<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = ["university_name", "Location"];
    use HasFactory;

    public function faculties()
    {
        return $this->hasMany(Faculty::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }
}
