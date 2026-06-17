<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'author',
        'text',
        'rating',
        'review_date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
