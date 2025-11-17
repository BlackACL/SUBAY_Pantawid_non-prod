<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity_time');
            $currentTime = time();
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds

            if ($lastActivity && ($currentTime - $lastActivity) > $timeout) {
                // Session has timed out
                Session::flush();
                Auth::logout();
                
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Your session has expired due to inactivity. Please login again.');
            }

            // Update last activity time
            Session::put('last_activity_time', $currentTime);
        }

        return $next($request);
    }
}
