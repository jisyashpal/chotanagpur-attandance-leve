<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAppKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appKey = env('API_APP_KEY', 'default-secret-key'); // Fallback if not set
        
        if ($request->header('X-APP-KEY') !== $appKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access. Invalid App Key.'
            ], 403);
        }

        return $next($request);
    }
}
