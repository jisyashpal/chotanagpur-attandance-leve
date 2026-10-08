<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is logged in and profile is not complete
        if ($user && !$user->is_profile_complete) {
            
            // Except for the profile completion routes and logout
            if (!$request->routeIs('*.profile.complete.*') && !$request->routeIs('logout')) {
                return redirect()->route($user->role . '.profile.complete.show');
            }
        }

        return $next($request);
    }
}
