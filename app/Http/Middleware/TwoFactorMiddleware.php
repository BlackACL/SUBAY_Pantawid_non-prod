<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect to verify if user is authenticated AND has a pending 2FA code
        if (auth()->check() && auth()->user()->two_factor_code && 
            auth()->user()->two_factor_expires_at && 
            auth()->user()->two_factor_expires_at->isFuture()) {
            return redirect()->route('verify');
        }

        return $next($request);
    }
} 