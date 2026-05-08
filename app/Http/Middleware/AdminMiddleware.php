<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if the user is authenticated
        if (!auth()->check()) {
            return redirect('login');
        }

        // 2. Check if the user is banned
        if (auth()->user()->is_banned) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been banned for violating community guidelines.']);
        }

        // 3. Check if the user is an admin
        if (!auth()->user()->is_admin) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}