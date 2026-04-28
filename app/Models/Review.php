<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'parent_id', 'movie_id', 'movie_title', 'rating', 'comment', 'movie_poster'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function parent() {
        return $this->belongsTo(Review::class, 'parent_id');
    }

    public function replies() {
        return $this->hasMany(Review::class, 'parent_id')->latest();
    }
}