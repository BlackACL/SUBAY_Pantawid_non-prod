<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user instanceof User
            && $user->two_factor_code
            && $user->two_factor_expires_at
            && $user->two_factor_expires_at->isFuture()
        ) {
            // Avoid redirect loop: don't redirect if we're already on the verify pages
            if (! $request->routeIs(['verify', 'verify.process', 'verify.resend'])) {
                return redirect()->route('verify');
            }
        }

        return $next($request);
    }
}
