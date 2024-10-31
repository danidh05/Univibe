<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutusDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'about_us_id',
    ];
    protected $table = "aboutusDetails";

    public function aboutUs()
    {
        return $this->belongsTo(AboutUs::class, 'about_us_id');
    }
}
