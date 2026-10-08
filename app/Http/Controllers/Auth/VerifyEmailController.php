<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $route = 'student.dashboard';
        if ($user->role === 'admin') $route = 'admin.dashboard';
        elseif ($user->role === 'faculty') $route = 'faculty.dashboard';
        elseif ($user->role === 'staff') $route = 'staff.dashboard';

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route($route, absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route($route, absolute: false).'?verified=1');
    }
}
