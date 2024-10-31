<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aboutus extends Model
{
    use HasFactory;
    protected $fillable = ["title"];
    protected $table = 'aboutus';
    public function details()
    {
        return $this->hasMany(AboutusDetails::class, 'about_us_id');
    }
}