<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'title',
        'rating',
        'reviews_count',
        'ratings_count',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
