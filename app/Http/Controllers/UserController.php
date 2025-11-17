<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SendPasswordNotification;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use App\Models\ImportProgress;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use App\Events\UserImported;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Trim search input to avoid hidden spaces
        $search = trim($request->get('search', ''));

        // Apply dropdown filters
        $province = $request->get('province');
        $municipality = $request->get('municipality');
        $office = $request->get('office');

        $usersQuery = User::active()->where('id', '!=', auth()->user()->id);

        // 🔎 Search
        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('fullname', 'LIKE', "%{$search}%")
                    ->orWhere('company_id', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 🔎 Apply filters
        if ($province) {
            $usersQuery->where('province', $province);
        }
        if ($municipality) {
            $usersQuery->where('municipality', $municipality);
        }
        if ($office) {
            $usersQuery->where('office', $office);
        }

        $users = $usersQuery->paginate(10);

        // ✅ If nothing found, check archived
        if ($search && $users->isEmpty()) {
            $archived = User::where('activated', 'No')
                ->where(function ($query) use ($search) {
                    $query->where('fullname', 'LIKE', "%{$search}%")
                        ->orWhere('company_id', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->first();

            if ($archived) {
                return redirect()->route('users')
                    ->with('error', 'The user exists but is archived.');
            } else {
                return redirect()->route('users')
                    ->with('error', 'The user does not exist.');
            }
        }

        // ✅ Build dynamic province-municipality map from Place model
        $provinces = Place::where('type', Place::TYPE_PROVINCE)
            ->with(['children' => function($query) {
                $query->where('type', Place::TYPE_MUNICIPALITY)
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $provinceMunicipalityMap = [];
        foreach ($provinces as $province) {
            $provinceMunicipalityMap[$province->name] = $province->children->pluck('name')->toArray();
        }

        // ✅ Build dynamic municipality-office map from Place model
        $municipalities = Place::where('type', Place::TYPE_MUNICIPALITY)
            ->with(['children' => function($query) {
                $query->where('type', Place::TYPE_OFFICE)
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $officeMap = [];
        foreach ($municipalities as $municipality) {
            $officeMap[$municipality->name] = $municipality->children->pluck('name')->toArray();
        }

        return view('superadmin.users_nav.users', compact('users', 'provinceMunicipalityMap', 'officeMap'));
    }

    public function archives(Request $request)
    {
        $query = User::where('activated', 'No');

        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }
        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }
        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_id', 'like', "%$search%")
                ->orWhere('fullname', 'like', "%$search%");
            });
        }

        $archivedUsers = $query->paginate(10);

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
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'company_id' => 'required|string|max:255|unique:users,company_id',
                'office' => 'required|string|max:255',
                'region' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'municipality' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'employee_status' => 'required|string|max:255',
                'access_level' => 'required|string|max:255',
                'activated' => 'required|string|in:Yes,No',
                'locked_status' => 'required|string|in:Yes,No',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('users')->withErrors($e->validator)->withInput()->with('openModal', true);
        }

        // Compose fullname
        $fullname = User::composeFullname($validated['last_name'], $validated['first_name'], $validated['middle_name'] ?? '');

        // Generate random password
        $password = Str::random(12);

        $user = new User();
        // fill fields that exist on model
        $user->fullname = $fullname;
        $user->username = $validated['username'];
        $user->company_id = $validated['company_id'];
        $user->office = $validated['office'];
        $user->region = $validated['region'];
        $user->province = $validated['province'];
        $user->municipality = $validated['municipality'];
        $user->email = $validated['email'];
        $user->employee_status = $validated['employee_status'];
        $user->access_level = $validated['access_level'];
        $user->activated = $validated['activated'];
        $user->locked_status = $validated['locked_status'];

        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();

        $user->assignRole($validated['access_level']);
        $user->notify(new SendPasswordNotification($password));

        activity()->causedBy(auth()->user())->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'access_level' => $user->access_level
            ])->log('Added new user');

        return redirect()->route('users')->with('success', 'User created successfully and password sent to email.');
    }

    /**
     * ✅ NEW: Update user details (Logic 2 - edit functionality)
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'company_id' => 'nullable|string|max:255|unique:users,company_id,' . $user->id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'employee_status' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'access_level' => 'required|string|max:255',
            'activated' => 'required|string|in:Yes,No',
            'locked_status' => 'required|string|in:Yes,No',
        ]);

        // Build fullname from parts (use your model helper if available)
        $fullname = User::composeFullname(
            $validated['last_name'] ?? '',
            $validated['first_name'] ?? '',
            $validated['middle_name'] ?? ''
        );

        unset($validated['first_name'], $validated['middle_name'], $validated['last_name']);

        // Detect unlock
        $wasLocked = $user->locked_status === 'Yes';
        $user->fullname = $fullname;
        $user->fill($validated);

        // If unlocking, send password reset link
        if ($wasLocked && $validated['locked_status'] === 'No') {
            Password::sendResetLink(['email' => $user->email]);
        }

        $user->save();
        
        // Update user role assignment when access_level changes
        $user->syncRoles([$validated['access_level']]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'user' => $user->fresh()]);
        }

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Show user profile for modal.
     */
    public function showProfile(User $user)
    {
        // Return JSON with split parts to help JS prefill the edit modal
        // Attempt to split using your User::decomposeFullname if available; otherwise simple parse:
        $first = '';
        $middle = '';
        $last = '';

        if (method_exists(User::class, 'decomposeFullname')) {
            $parts = User::decomposeFullname($user->fullname);
            $first = $parts['first_name'] ?? '';
            $middle = $parts['middle_name'] ?? '';
            $last = $parts['last_name'] ?? '';
        } else {
            // fallback: attempt to split "LASTNAME , FIRST MIDDLE"
            if (strpos($user->fullname ?? '', ',') !== false) {
                [$last, $rest] = array_map('trim', explode(',', $user->fullname, 2));
                $rp = preg_split('/\s+/', trim($rest));
                $first = $rp[0] ?? '';
                $middle = count($rp) > 1 ? implode(' ', array_slice($rp, 1)) : '';
            } else {
                // naive fallback: first middle last
                $parts = preg_split('/\s+/', trim($user->fullname ?? ''));
                if (count($parts) === 1) { $first = $parts[0]; }
                elseif (count($parts) === 2) { $first = $parts[0]; $last = $parts[1]; }
                else { $first = array_shift($parts); $last = array_pop($parts); $middle = implode(' ', $parts); }
            }
        }

        return response()->json([
            'success' => true,
            'user' => array_merge($user->toArray(), [
                'first_name' => $first,
                'middle_name' => $middle,
                'last_name' => $last,
            ])
        ]);
    }

    public function archive(User $user)
    {
        $user->update(['activated' => 'No', 'archived_at' => now()]);

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
    
    /**
     * Return user's history (units and fets or whatever relations you keep).
     */
    public function history($id)
    {
        try {
            $user = User::where('id', $id)->first();
            if (!$user) {
                Log::warning("User not found for history: $id");
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            $units = method_exists($user, 'units') ? $user->units()->get() : [];
            $fets  = method_exists($user, 'fets') ? $user->fets()->get() : [];

            Log::info("History for user $id: units=" . $units->count() . ", fets=" . $fets->count());

            return response()->json([
                'success' => true,
                'history' => [
                    'units' => $units,
                    'fets'  => $fets,
                ],
                'user' => $user->only(['id','fullname','company_id'])
            ]);
        } catch (\Throwable $e) {
            Log::error("Error loading history for user $id: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function unarchive(Request $request, User $user)
    {
        $user->update(['activated' => 'Yes', 'archived_at' => null]);

        activity()->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'fullname' => $user->fullname,
            ])->log('Restored user');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User restored', 'user' => $user->fresh()]);
        }

        return redirect()->route('archives')->with('success', 'User restored successfully');
    }

    // Import Profiles Modal on Superadmin
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:20480',
            ]);

            $file = $request->file('csv_file');
            if (!$file) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            // Read CSV in binary mode to preserve exact bytes from the file
            $handle = fopen($file->getRealPath(), 'rb');
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
            
            $header = array_map(fn($h) => strtolower(trim($h)), array_shift($rows));
            
            // Debug: Log the CSV headers
            Log::info('CSV Headers detected: ' . json_encode($header));
            
            $records = [];
            foreach ($rows as $row) {
                if (count($header) === count($row)) {
                    $records[] = array_combine($header, $row);
                } else {
                    Log::warning('Row count mismatch: Headers=' . count($header) . ', Row=' . count($row));
                }
            }
            $total = count($records);
            
            Log::info("Total records to process: {$total}");

        // Quick test to see if User model works
        try {
            $testUser = [
                'fullname' => 'TEST USER',
                'username' => 'testuser' . time(),
                'company_id' => 'TEST' . time(),
                'email' => 'test' . time() . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'employee_status' => 'Active',
                'office' => 'Test Office',
                'region' => 'Test Region', 
                'province' => 'Test Province',
                'municipality' => 'Test Municipality',
                'access_level' => 'Employee',
                'activated' => 1,
                'locked_status' => 0,
            ];
            
            $createdTest = User::create($testUser);
            Log::info('Test user created successfully with ID: ' . $createdTest->id);
            // Delete test user immediately
            $createdTest->delete();
        } catch (\Exception $e) {
            Log::error('Test user creation failed: ' . $e->getMessage());
        }

        ImportProgress::updateOrCreate(
            ['type' => 'fets_import'],
            ['total' => $total, 'processed' => 0, 'progress' => 0, 'recent' => '', 'skipped' => []]
        );

        $skipped = [];
        $updated = [];
        $processed = 0;
        
        foreach ($records as $index => $record) {
            // Just trim whitespace, don't modify the actual data
            $record = array_map('trim', $record);

            // Check for existing user first
            $existingUser = User::where('email', $record['email'])->first();

            $validator = Validator::make($record, [
                'fullname' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'company_id' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'company_id')->ignore($existingUser?->id)
                ],
                'office' => 'required|string|max:255',
                'region' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'municipality' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($existingUser?->id)
                ],
                'employee_status' => 'required|string|max:255',
                'access_level' => 'required|string|max:255',
                'activated' => 'required|string|in:Yes,No',
                'locked_status' => 'required|string|in:Yes,No',
            ]);

            if ($validator->fails()) {
                $skipped[] = [
                    'errors' => $validator->errors()->all(),
                    'record' => $record
                ];
                
                // Update progress even for skipped records
                $processed++;
                $progress = round(($processed / $total) * 100);
                ImportProgress::where('type', 'fets_import')->update([
                    'processed' => $processed,
                    'progress' => $progress,
                    'recent' => 'Skipped: ' . ($record['fullname'] ?? 'Unknown')
                ]);
                continue;
            }

            $validated = $validator->validated();

            if ($existingUser) {
                // Check if key credentials match (fullname, username, company_id)
                $keyCredentialsMatch =
                    $existingUser->fullname === $validated['fullname'] &&
                    $existingUser->username === $validated['username'] &&
                    $existingUser->company_id === $validated['company_id'];

                if ($keyCredentialsMatch) {
                    // Convert Yes/No to boolean values for update
                    $validated['activated'] = $validated['activated'] === 'Yes' ? 1 : 0;
                    $validated['locked_status'] = $validated['locked_status'] === 'Yes' ? 1 : 0;
                    
                    // Update existing user
                    $existingUser->update($validated);
                    
                    // Track as updated
                    $updated[] = [
                        'message' => "{$validated['fullname']} ({$validated['email']}) was updated successfully.",
                        'record' => $validated
                    ];
                    
                    // Update progress for updated users
                    $processed++;
                    $progress = round(($processed / $total) * 100);
                    ImportProgress::where('type', 'fets_import')->update([
                        'processed' => $processed,
                        'progress' => $progress,
                        'recent' => 'Updated: ' . $validated['fullname']
                    ]);
                } else {
                    $skipped[] = [
                        'errors' => ["{$validated['fullname']} ({$validated['email']}) is already existing with different key credentials (fullname, username, or company_id)."],
                        'record' => $validated
                    ];
                    
                    // Update progress for skipped existing users
                    $processed++;
                    $progress = round(($processed / $total) * 100);
                    ImportProgress::where('type', 'fets_import')->update([
                        'processed' => $processed,
                        'progress' => $progress,
                        'recent' => 'Skipped: ' . $validated['fullname'] . ' (credential mismatch)'
                    ]);
                    continue;
                }
                } else {
                try {
                    $password = Str::random(12);
                    $validated['password'] = Hash::make($password);
                    $validated['email_verified_at'] = now();
                    
                    // Convert Yes/No to boolean values
                    $validated['activated'] = $validated['activated'] === 'Yes' ? 1 : 0;
                    $validated['locked_status'] = $validated['locked_status'] === 'Yes' ? 1 : 0;
                    
                    Log::info('Attempting to create user: ' . json_encode($validated));
                    
                    $newUser = User::create($validated);
                    Log::info('User created successfully with ID: ' . $newUser->id);
                    
                    $newUser->assignRole($validated['access_level'] ?? 'Employee');
                    $newUser->notify(new SendPasswordNotification($password));
                } catch (\Exception $e) {
                    Log::error('Failed to create user: ' . $e->getMessage() . ' - Data: ' . json_encode($validated));
                    $skipped[] = [
                        'errors' => ['Failed to create user: ' . $e->getMessage()],
                        'record' => $validated
                    ];
                }
            }            // Update progress
            $processed++;
            $progress = round(($processed / $total) * 100);
            ImportProgress::where('type', 'fets_import')->update([
                'processed' => $processed,
                'progress' => $progress,
                'recent' => 'Processed: ' . $validated['fullname']
            ]);
            
            // Add small delay to make progress visible
            usleep(10000); // 0.01 second delay
        }

        ImportProgress::where('type', 'fets_import')->update([
            'skipped' => $skipped,
            'updated' => $updated
        ]);

        Log::info("Import completed. Total processed: {$processed}, Updated: " . count($updated) . ", Skipped: " . count($skipped));

        return response()->json([
            'status' => 'done', 
            'updated' => collect($updated)->map(function($u) {
                return $u['message'] ?? 'Updated successfully';
            })->toArray(),
            'skipped' => collect($skipped)->map(function($s) {
            return $s['errors'][0] ?? 'Unknown error';
        })->toArray()]);
        
        } catch (\Exception $e) {
            Log::error('Import failed with exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
        
    // SUPER SIMPLE TEST METHOD
    public function importSimple(Request $request)
    {
        // Just create ONE test user to see if database works
        try {
            $timestamp = time();
            
            $userId = DB::table('users')->insertGetId([
                'fullname' => 'TEST USER ' . $timestamp,
                'username' => 'test' . $timestamp,
                'company_id' => 'TEST' . $timestamp,
                'email' => 'test' . $timestamp . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'employee_status' => 'Active',
                'office' => 'Test Office',
                'region' => 'Test Region',
                'province' => 'Test Province',
                'municipality' => 'Test Municipality',
                'access_level' => 'Employee',
                'activated' => 1,
                'locked_status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Update progress to show completion
            ImportProgress::updateOrCreate(
                ['type' => 'fets_import'],
                ['total' => 1, 'processed' => 1, 'progress' => 100, 'recent' => 'Test user created with ID: ' . $userId]
            );
            
            return response()->json([
                'status' => 'done',
                'created' => 1,
                'user_id' => $userId,
                'message' => 'Test user created successfully! Check users table for ID: ' . $userId
            ]);
            
        } catch (\Exception $e) {
            ImportProgress::updateOrCreate(
                ['type' => 'fets_import'],
                ['total' => 1, 'processed' => 0, 'progress' => 0, 'recent' => 'ERROR: ' . $e->getMessage()]
            );
            
            return response()->json([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testImport()
    {
        // Force write to log file to confirm method is hit
        file_put_contents(storage_path('logs/test_debug.log'), "testImport method called at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        
        try {
            // Log that we hit this method
            Log::info('TEST IMPORT: Method called successfully');
            
            // Try to create a simple test user with unique id to avoid conflicts
            $testUser = [
                'id_number' => 'TEST' . time(),
                'fullname' => 'Test User ' . date('H:i:s'),
                'position' => 'Test Position',  
                'office' => 'Test Office',
                'email' => 'test' . time() . '@example.com',
                'role' => 'employee',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            DB::table('users')->insert($testUser);
            Log::info('TEST IMPORT: Test user created successfully');
            
            file_put_contents(storage_path('logs/test_debug.log'), "Test user created successfully\n", FILE_APPEND);
            
            return redirect()->back()->with('success', 'Test import successful! User created: ' . $testUser['fullname']);
            
        } catch (\Exception $e) {
            Log::error('TEST IMPORT ERROR: ' . $e->getMessage());
            file_put_contents(storage_path('logs/test_debug.log'), "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            return redirect()->back()->with('error', 'Test import failed: ' . $e->getMessage());
        }
    }
}
