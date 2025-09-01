<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SendPasswordNotification;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use App\Models\ImportProgress;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Trim search input to avoid hidden spaces
        $search = trim($request->get('search', ''));

            if ($search) {
                // Search mode
                $users = User::active()
                    ->where('id', '!=', auth()->user()->id)
                    ->where(function ($query) use ($search) {
                        $query->where('id', 'LIKE', "%{$search}%")
                            ->orWhere('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('company_id', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->paginate(10)
                    ->withQueryString();

                // If nothing found, check archived
                if ($users->isEmpty()) {
                    $archived = User::where('deleted_status', 'Yes')
                        ->where(function ($query) use ($search) {
                            $query->where('fullname', 'LIKE', "%{$search}%")
                                ->orWhere('company_id', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                        })
                        ->first();

                    if ($archived) {
                        return redirect()->route('users')
                            ->with('error', 'The user exists but is archived/deleted.');
                    } else {
                        return redirect()->route('users')
                            ->with('error', 'The user is not exist.');
                    }
                }
            } else {
                // Default: show all active users
                $users = User::active()
                    ->where('id', '!=', auth()->user()->id)
                    ->latest()
                    ->paginate(10);
            }

        return view('superadmin.users_nav.users', compact('users'));
    }

    public function archives(Request $request)
    {
        $search = $request->get('search');

        if ($search) {
            $archivedUsers = User::archived()
                ->where(function ($query) use ($search) {
                    $query->where('company_id', 'LIKE', "%{$search}%")
                        ->orWhere('fullname', 'LIKE', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString();
        } else {
            $archivedUsers = User::archived()->latest()->paginate(10);
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

        try {
            $validated = $request->validate([
                'fullname' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'company_id' => 'required|string|max:255|unique:users,company_id',
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('users')->withErrors($e->validator)->withInput()->with('openModal', true);
        }

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

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'access_level' => $user->access_level
            ])
            ->log('Added new user');

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

    public function archive(User $user)
    {
        $user->archive();
        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'access_level' => $user->access_level
            ])
            ->log('Archived user');
        return redirect()->route('users')->with('success', 'User archived successfully');
    }

    public function unarchive(User $user)
    {
        $user->unarchive();
        return redirect()->route('archives')->with('success', 'User restored successfully');
    }

    // Import Profiles Modal on Superadmin
    public function import(Request $request)
    {
        // 🔎 Debugging logs
        Log::info('Import request received', $request->all());

        if ($request->hasFile('csv_file')) {
            Log::info('CSV file uploaded: ' . $request->file('csv_file')->getClientOriginalName());
        } else {
            Log::error('No CSV file found in request');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $file = $request->file('csv_file');

        if (!$file) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        // Parse CSV
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        // Normalize headers (trim spaces + lowercase)
        $header = array_map(fn($h) => strtolower(trim($h)), array_shift($rows));

        $records = [];

        foreach ($rows as $row) {
            $records[] = array_combine($header, $row);
        }

        $total = count($records);

        ImportProgress::updateOrCreate(
            ['type' => 'fets_import'],
            ['total' => $total, 'processed' => 0]
        );

        $skipped = [];

        foreach ($records as $index => $record) {
            $record = array_map(fn($v) => mb_convert_encoding($v, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'), $record);

            $validator = Validator::make($record, [
                'fullname'        => 'required|string|max:255',
                'username'        => 'required|string|max:255',
                'company_id'      => 'required|string|max:255',
                'office'          => 'required|string|max:255',
                'region'          => 'required|string|max:255',
                'province'        => 'required|string|max:255',
                'municipality'    => 'required|string|max:255',
                'email'           => 'required|email|max:255',
                'employee_status' => 'required|string|max:255',
                'access_level'    => 'required|string|max:255',
                'activated'       => 'required|string|max:255',
                'locked_status'   => 'required|string|max:255',
                'deleted_status'  => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $skipped[] = [
                    'errors' => $validator->errors()->all(),
                    'record' => $record
                ];
                continue;
            }

            $validated = $validator->validated();

            // 🔹 Check if user exists by email
            $existingUser = User::where('email', $validated['email'])->first();

            if ($existingUser) {
                // Compare credentials
                $allMatch =
                    $existingUser->fullname === $validated['fullname'] &&
                    $existingUser->username === $validated['username'] &&
                    $existingUser->company_id === $validated['company_id'];

                if ($allMatch) {
                    // ✅ Update user info
                    $existingUser->update($validated);
                } else {
                    // ⚠️ Skip if credentials conflict
                    $skipped[] = [
                        'errors' => ["{$validated['fullname']} ({$validated['email']}) is already existing with different credentials."],
                        'record' => $validated
                    ];
                    continue;
                }
            } else {
                // 🔹 Insert new user
                $password = Str::random(12);
                $newUser = new User($validated);
                $newUser->password = Hash::make($password);
                $newUser->email_verified_at = now();
                $newUser->save();

                $newUser->assignRole($validated['access_level'] ?? 'Employee');
                $newUser->notify(new SendPasswordNotification($password));
            }

            ImportProgress::where('type', 'fets_import')
                ->update(['processed' => $index + 1]);
        }

        if (!empty($skipped)) {
            return response()->json([
                'status' => 'done',
                'skipped' => collect($skipped)->map(function($s) {
                    return $s['errors'][0] ?? 'Unknown error';
                })->toArray()
            ]);
        }

        return response()->json(['status' => 'done', 'skipped' => []]);
    }

}
