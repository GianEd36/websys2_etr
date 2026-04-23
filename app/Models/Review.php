<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    //
    protected $fillable = [
        'movie_id', 
        'user_id',
        'user_name', 
        'rating', 
        'comment', 
        'movie_title',
        'movie_poster'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
