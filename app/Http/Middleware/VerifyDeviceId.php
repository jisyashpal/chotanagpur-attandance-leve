<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeviceId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only verify if user is authenticated (Sanctum)
        if ($user) {
            $headerDeviceId = $request->header('X-DEVICE-ID');

            if (!$headerDeviceId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device verification failed. X-DEVICE-ID header is missing.',
                    'code' => 'DEVICE_HEADER_MISSING'
                ], 403);
            }

            // Check if this device is registered for the user
            if (!$user->devices()->where('device_id', $headerDeviceId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device verification failed. Unrecognized device.',
                    'code' => 'DEVICE_MISMATCH'
                ], 403);
            }
        }

        return $next($request);
    }
}
