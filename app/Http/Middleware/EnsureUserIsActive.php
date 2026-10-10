<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Log out users whose accounts have been blocked.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->is('api/*')) {
                $token = $user->currentAccessToken();
                if ($token instanceof PersonalAccessToken) {
                    $token->delete();
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account has been blocked.',
                ], 401);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been blocked.',
            ]);
        }

        return $next($request);
    }
}
