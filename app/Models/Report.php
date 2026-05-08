<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['user_id', 'review_id', 'reason'];

    public function user() { 
        return $this->belongsTo(User::class); 
    } // The Reporter
    public function review() { 
        return $this->belongsTo(Review::class); 
    } // The Critique
}
