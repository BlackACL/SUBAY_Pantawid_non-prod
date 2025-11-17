<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SingleDeviceLogin
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
            $user = Auth::user();
            $currentSessionId = Session::getId();
            
            // Check if user has a stored session ID
            if ($user->session_id && $user->session_id !== $currentSessionId) {
                // User is logged in from another device
                Session::flush();
                Auth::logout();
                
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Your account has been logged in from another device. Only one device is allowed at a time.');
            }
        }

        return $next($request);
    }
}
