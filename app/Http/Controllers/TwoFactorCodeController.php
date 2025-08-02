<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class TwoFactorCodeController extends Controller
{
    public function verify()
    {




        return view('auth.two-factor-challenge');
    }




    public function process(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'required|digits:6',
        ]);

        /** @var User $user */
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->withErrors(['two_factor_code' => 'User not authenticated.']);
        }

        if ($user->two_factor_code !== $request->two_factor_code) {
            return back()->withErrors(['two_factor_code' => 'The code you entered is incorrect.']);
        }

        if ($user->two_factor_expires_at->lt(now())) {
            return back()->withErrors(['two_factor_code' => 'The code has expired.']);
        }

        // Clear the code after successful verification
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        // return redirect()->intended('/dashboard');
        if (
            $user->hasRole('superadmin')
        ) {
            return redirect('/logs')->with('success', 'Login successful');
        } elseif ($user->hasRole('Regional DPSC')) {
            return redirect('/Regional/VerifiedFETS')->with('success', 'Login successful');
        } elseif ($user->hasRole('Provincial DPSC')) {
            return redirect('/FETSrequest')->with('success', 'Login successful');
        } else {
            return redirect('/Inventory')->with('success', 'Login successful');
        }
    }

    public function resend(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->withErrors(['two_factor_code' => 'User not authenticated.']);
        }

        // Generate a new code
        $user->regenerateTwoFactorCode();

        // Send the new code via email
        $user->notify(new \App\Notifications\TwoFactorCodeNotification());

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
