<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendPasswordNotification;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        if ($search) {
            $users = User::active()
                ->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%{$search}%")
                        ->orWhere('fullname', 'LIKE', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString();
        } else {
            $users = User::active()->paginate(10);
        }

        return view('superadmin.users_nav.users', compact('users'));
    }

    public function archives(Request $request)
    {
        $search = $request->get('search');

        if ($search) {
            $archivedUsers = User::archived()
                ->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%{$search}%")
                        ->orWhere('fullname', 'LIKE', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString();
        } else {
            $archivedUsers = User::archived()->paginate(10);
        }

        return view('superadmin.archives_nav.archives', compact('archivedUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.users_nav.addusers');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'company_id' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'employee_status' => 'required|string|max:255',
            'access_level' => 'required|string|max:255',
            'activated' => 'required|string|max:255',
            'locked_status' => 'required|string|max:255',
            'deleted_status' => 'required|string|max:255',
        ]);

        // Generate a strong random password
        $password = Str::random(12);
        // Optionally, you can use a custom generator to ensure all requirements
        // $password = $this->generateStrongPassword();

        $user = new User($validated);

        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();

        // Assign role based on access_level
        $roleName = $validated['access_level'];
        $user->assignRole($roleName);

        // Send password to user's email
        $user->notify(new SendPasswordNotification($password));

        return redirect()->route('users')->with('success', 'User created successfully and password sent to email.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show user profile for modal.
     */
    public function showProfile(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function archive(User $user)
    {
        $user->archive();
        return redirect()->route('users')->with('success', 'User archived successfully');
    }

    public function unarchive(User $user)
    {
        $user->unarchive();
        return redirect()->route('archives')->with('success', 'User restored successfully');
    }
}
