<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;
    protected $fillable = ['link', "description", "major_id", "title", "company"];
    public function major()
    {
        return $this->belongsTo(Major::class);
    }
}
