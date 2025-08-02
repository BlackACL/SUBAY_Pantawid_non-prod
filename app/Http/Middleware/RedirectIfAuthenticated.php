<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $previous = url()->previous();
                $current = $request->url();
                // Avoid redirect loop or redirecting to login/logout
                if (str_contains($previous, '/login') || str_contains($previous, '/logout') || $previous == $current) {
                    return redirect('/users');
                }
                return redirect($previous);
            }
        }

        return $next($request);
    }
}
