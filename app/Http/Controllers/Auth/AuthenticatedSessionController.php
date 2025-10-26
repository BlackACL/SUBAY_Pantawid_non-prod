<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // ✅ Step 1: Check if email exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'The email is not registered! You can email the admin with this concern!',
            ])->onlyInput('email');
        }

        // ✅ Step 2: Check if account is locked
        if ($user->isLocked()) {
            return back()->withErrors([
                'email' => 'Your account has been locked. Please email the admin to unlock your account!',
            ])->onlyInput('email');
        }

        // ✅ Step 3: Try to authenticate
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Wrong password → increment failed attempts
            $user->increment('failed_attempts');

            if ($user->failed_attempts >= 3) {
                $user->lock(); // calls User::lock()
                return back()->withErrors([
                    'email' => 'Your account has been locked. Please email the admin to unlock your account!',
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => 'Invalid credentials. Attempt '.$user->failed_attempts.'/3',
            ])->onlyInput('email');
        }

        // ✅ Step 4: Successful login → reset counter
        $user->failed_attempts = 0;
        $user->save();

        $request->session()->regenerate();

        $user->regenerateTwoFactorCode();
        $user->notify(new \App\Notifications\TwoFactorCodeNotification());

        // ✅ Log user login
        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
                'device' => $request->userAgent(),
            ])
            ->log('Logged In');

        return redirect()->route('verify');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log user logout
        if (auth()->check()) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'device' => $request->userAgent(),
                ])
                ->log('Logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
