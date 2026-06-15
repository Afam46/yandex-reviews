<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
