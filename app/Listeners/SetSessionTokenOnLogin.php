<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Str;

class SetSessionTokenOnLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;

        if (!$user->session_token) {
            $user->session_token = Str::random(60);
            $user->save();
        }

        // Store the user's session token in the session so it can be validated
        session(['session_token' => $user->session_token]);
    }
}
