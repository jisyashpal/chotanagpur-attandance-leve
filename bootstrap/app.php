<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\CheckProfileCompletion;
use App\Http\Middleware\VerifyAppKey;
use App\Http\Middleware\EnsureJsonResponse;
use App\Http\Middleware\VerifyDeviceId;
use App\Http\Middleware\EnsureUserIsActive;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'profile.complete' => CheckProfileCompletion::class,
            'api.key' => VerifyAppKey::class,
            'json.response' => EnsureJsonResponse::class,
            'device.verify' => VerifyDeviceId::class,
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->api(prepend: [
            VerifyAppKey::class,
            EnsureJsonResponse::class,
        ]);

        $middleware->throttleApi('api');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();
