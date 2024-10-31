<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Major extends Model
{
    use HasFactory;
    protected $fillable = ['major_name', 'faculty_id'];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}
