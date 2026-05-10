<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // If user is banned, allow only the banned notice and appeal routes while keeping them signed-in.
            if ($user->is_banned) {
                $allowed = $request->routeIs('banned.notice') || $request->routeIs('banned.appeal') || $request->is('banned') || $request->is('banned/*');
                if ($allowed) {
                    return $next($request);
                }

                // Any other route: redirect to banned notice but keep the session intact
                return redirect()->route('banned.notice');
            }
        }

        return $next($request);
    }
}
