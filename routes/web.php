<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FetsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorCodeController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RepairDestinationController;
use App\Http\Controllers\ManualController;
use App\Models\ImportProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// CSV IMPORT ROUTE - Working version with progress tracking and email
Route::post('/emergency-test', function(\Illuminate\Http\Request $request) {
    try {
        // Check if file was uploaded
        if (!$request->hasFile('csv_file')) {
            return redirect()->back()->with('error', 'No CSV file uploaded');
        }

        $file = $request->file('csv_file');

        // Read CSV data
        $csvData = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", $csvData));
        $header = array_shift($rows); // Remove header row

        // Filter out empty rows
        $rows = array_filter($rows, function($row) {
            return !empty(array_filter($row));
        });

        $total = count($rows);
        $processed = 0;
        $created = 0;
        $skipped = 0;
        $transparencyLogs = []; // Store logs for transparency

        // Initialize progress - clear any existing progress first
        Log::info("Starting CSV import with {$total} rows");

        // Start a database transaction
        DB::beginTransaction();

        ImportProgress::updateOrCreate(
            ['type' => 'fets_import'],
            [
                'total' => $total,
                'processed' => 0,
                'progress' => 0,
                'recent' => "Starting import of {$total} records...",
                'logs' => json_encode([]),
                'updated_at' => now()
            ]
        );

        // Commit initial progress
        DB::commit();

        foreach ($rows as $index => $row) {
            // Start transaction for each user
            DB::beginTransaction();
            try {
                Log::info("Processing row " . ($index + 1) . ": " . json_encode($row));

                // Generate random password
                $randomPassword = Str::random(12);

                // Extract data from CSV row
                $email = trim($row[2] ?? '');
                $fullname = trim($row[3] ?? 'Unknown User');
                $accessLevel = trim($row[9] ?? 'Employee');

                Log::info("Extracted - Email: $email, Fullname: $fullname, AccessLevel: $accessLevel");

                // Skip if no email
                if (empty($email)) {
                    DB::rollback(); // Rollback current transaction

                    Log::warning("Skipping row " . ($index + 1) . " - No email provided");
                    $skipped++;
                    $processed++;

                    // Add to transparency logs
                    $transparencyLogs[] = [
                        'status' => 'skipped',
                        'message' => "Row " . ($index + 1) . " - No email provided",
                        'timestamp' => now()->toISOString()
                    ];

                    // Start new transaction for progress update
                    DB::beginTransaction();

                    // Update progress even for skipped users
                    $progress = round(($processed / $total) * 100);
                    ImportProgress::updateOrCreate(
                        ['type' => 'fets_import'],
                        [
                            'processed' => $processed,
                            'progress' => $progress,
                            'recent' => "Skipped {$processed}/{$total}: No email provided",
                            'logs' => json_encode(array_slice($transparencyLogs, -100)) // Keep last 100 logs
                        ]
                    );

                    DB::commit(); // Commit progress update
                    Log::info("Skip progress updated: {$processed}/{$total} ({$progress}%)");
                    usleep(200000); // 200ms delay for skip case

                    continue;
                }

                // Check if user already exists by email OR company_id
                $companyId = trim($row[0] ?? '') ?: 'TEMP-' . time() . '-' . rand(1000, 9999);
                $existingUserByEmail = User::where('email', $email)->first();
                $existingUserByCompanyId = User::where('company_id', $companyId)->first();

                if ($existingUserByEmail || $existingUserByCompanyId) {
                    DB::rollback(); // Rollback current transaction

                    $duplicateReason = '';
                    if ($existingUserByEmail && $existingUserByCompanyId) {
                        $duplicateReason = "email and company_id already exist";
                        Log::info("Skipping row " . ($index + 1) . " - User with email $email and company_id $companyId already exist");
                    } elseif ($existingUserByEmail) {
                        $duplicateReason = "email already exists";
                        Log::info("Skipping row " . ($index + 1) . " - User with email $email already exists");
                    } else {
                        $duplicateReason = "company_id already exists";
                        Log::info("Skipping row " . ($index + 1) . " - User with company_id $companyId already exists");
                    }

                    $skipped++;
                    $processed++;

                    // Add to transparency logs
                    $transparencyLogs[] = [
                        'status' => 'duplicate',
                        'message' => "Skipped: {$fullname} ({$email}) - {$duplicateReason}",
                        'timestamp' => now()->toISOString()
                    ];

                    // Start new transaction for progress update
                    DB::beginTransaction();

                    // Update progress for duplicate users
                    $progress = round(($processed / $total) * 100);
                    ImportProgress::updateOrCreate(
                        ['type' => 'fets_import'],
                        [
                            'processed' => $processed,
                            'progress' => $progress,
                            'recent' => "Skipped {$processed}/{$total}: {$fullname} (duplicate)",
                            'logs' => json_encode(array_slice($transparencyLogs, -100)) // Keep last 100 logs
                        ]
                    );

                    DB::commit(); // Commit progress update
                    Log::info("Duplicate skip progress updated: {$processed}/{$total} ({$progress}%)");
                    usleep(200000); // 200ms delay for duplicate case

                    continue;
                }

                // Create user using User model (proper way)
                $user = new User();
                $user->company_id = $companyId; // Use the already extracted and validated company_id
                $user->username = trim($row[1] ?? '') ?: 'user_' . time();
                $user->email = $email;
                $user->fullname = $fullname;
                $user->region = trim($row[4] ?? 'Region XI');
                $user->province = trim($row[5] ?? '');
                $user->municipality = trim($row[6] ?? '');
                $user->office = trim($row[7] ?? '');
                $user->employee_status = trim($row[8] ?? 'Regular');
                $user->access_level = $accessLevel;
                $user->activated = ($row[10] ?? 'Yes') === 'Yes' ? 'Yes' : 'No';
                $user->locked_status = ($row[11] ?? 'No') === 'Yes' ? 'Yes' : 'No';
                $user->password = Hash::make($randomPassword);
                $user->email_verified_at = now();

                // Save user
                $user->save();
                Log::info("User created successfully with ID: " . $user->id);

                // Assign role using Spatie
                $user->assignRole($accessLevel);
                Log::info("Role '$accessLevel' assigned to user ID: " . $user->id);

                // Send email with password (in background job for better performance)
                try {
                    $user->notify(new \App\Notifications\SendPasswordNotification($randomPassword));
                    Log::info("Password notification sent to: $email");
                } catch (\Exception $mailError) {
                    // Continue processing even if email fails
                    Log::warning("Failed to send email to {$email}: " . $mailError->getMessage());
                }

                $created++;
                $processed++;
                $progress = round(($processed / $total) * 100);

                // Add to transparency logs
                $transparencyLogs[] = [
                    'status' => 'created',
                    'message' => "Created: {$fullname} ({$email}) - Role: {$accessLevel}",
                    'timestamp' => now()->toISOString()
                ];

                // Update progress after successful user creation
                ImportProgress::updateOrCreate(
                    ['type' => 'fets_import'],
                    [
                        'processed' => $processed,
                        'progress' => $progress,
                        'recent' => "Created {$processed}/{$total}: {$fullname}",
                        'logs' => json_encode(array_slice($transparencyLogs, -100)) // Keep last 100 logs
                    ]
                );

                // Commit both user creation and progress update
                DB::commit();

                Log::info("Success progress updated: {$processed}/{$total} ({$progress}%)");

                // Small delay to allow frontend to catch real-time progress
                usleep(300000); // 300ms delay for created user

            } catch (\Exception $rowError) {
                // Rollback the current user transaction
                DB::rollback();

                Log::error("Failed to process row " . ($index + 1) . ": " . $rowError->getMessage());
                $skipped++;
                $processed++;

                // Add to transparency logs
                $transparencyLogs[] = [
                    'status' => 'error',
                    'message' => "Failed: Row " . ($index + 1) . " - " . substr($rowError->getMessage(), 0, 100),
                    'timestamp' => now()->toISOString()
                ];

                // Start new transaction for progress update
                DB::beginTransaction();

                // Update progress for failed rows
                $progress = round(($processed / $total) * 100);
                ImportProgress::updateOrCreate(
                    ['type' => 'fets_import'],
                    [
                        'processed' => $processed,
                        'progress' => $progress,
                        'recent' => "Error {$processed}/{$total}: Failed to process row",
                        'logs' => json_encode(array_slice($transparencyLogs, -100)) // Keep last 100 logs
                    ]
                );

                // Commit progress update
                DB::commit();

                Log::info("Error progress updated: {$processed}/{$total} ({$progress}%)");

                // Small delay for error case too
                usleep(200000); // 200ms delay for error case
            }
        }

        // Final progress update
        DB::beginTransaction();
        $transparencyLogs[] = [
            'status' => 'completed',
            'message' => "Import completed! Created: {$created}, Skipped: {$skipped}",
            'timestamp' => now()->toISOString()
        ];

        ImportProgress::updateOrCreate(
            ['type' => 'fets_import'],
            [
                'processed' => $processed,
                'progress' => 100,
                'recent' => "Import completed! Created: {$created}, Skipped: {$skipped}",
                'logs' => json_encode(array_slice($transparencyLogs, -100)) // Keep last 100 logs
            ]
        );
        DB::commit();

        Log::info("Final progress updated: 100% complete - Created: {$created}, Skipped: {$skipped}");

        // Check if request expects JSON (AJAX)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Import completed! Created: {$created} users, Skipped: {$skipped} duplicates"
            ]);
        }

        return redirect()->back()->with('success', "Import completed! Created: {$created} users, Skipped: {$skipped} duplicates");

    } catch (\Exception $e) {
        ImportProgress::updateOrCreate(
            ['type' => 'fets_import'],
            ['progress' => 0, 'recent' => 'ERROR: ' . $e->getMessage()]
        );

        // Check if request expects JSON (AJAX)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
    }
});

// Redirect root to login
Route::get('/', fn () => redirect('login'));

Route::post('/check-email', function (Request $request) {
    return \App\Models\User::where('email', $request->email)->exists()
        ? response()->json(['exists' => true])
        : response()->json(['exists' => false]);
});

// General Authenticated Routes
Route::middleware('auth')->group(function () {
    // 2FA Routes
    Route::get('/verify', [TwoFactorCodeController::class, 'verify'])->name('verify');
    Route::post('/verify', [TwoFactorCodeController::class, 'process'])->name('verify.process');
    Route::post('/verify/resend', [TwoFactorCodeController::class, 'resend'])->name('verify.resend');

    // Show the password edit form (all roles can access)
    Route::get('/password/edit', function () {
        return view('auth.update-password');
    })->middleware('auth')->name('password.edit');

    // Handle the update request (PATCH)
    Route::patch('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])
        ->middleware('auth')
        ->name('password.update');

    // FETS Selection/Generation (All Roles)
    Route::middleware(['verified', 'twofactor'])->group(function () {
        Route::get('/FETS', [FetsController::class, 'select'])->name('fets.select');
        Route::post('/FETS/generate', [FetsController::class, 'generate'])->name('fets.generate');
        Route::post('/FETS/update', [FetsController::class, 'update'])->name('fets.update');
    });
});

// 🔐 SUPERADMIN Routes
Route::middleware(['auth', 'role:superadmin', 'verified', 'twofactor'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'superadminDashboard'])->name('superadmin.dashboard');
    Route::get('/logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('superadmin.logs_nav.logs');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users/addusers', [UserController::class, 'store'])->name('addusers.store');
    Route::get('/users/{user}/profile', [UserController::class, 'showProfile'])->name('users.profile');
    Route::get('/archives', [UserController::class, 'archives'])->name('archives');
    Route::post('/users/{user}/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::post('/users/{user}/unarchive', [UserController::class, 'unarchive'])->name('users.unarchive');
    Route::post('/users/{user}', [UserController::class, 'update'])->name('users.update.post');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}/history', [UserController::class, 'history']);

    // ✅ Superadmin Inventory - View all employees' inventory
    Route::get('/superadmin/inventory', [InventoryController::class, 'superadminInventory'])->name('superadmin.inventory');

    // ✅ Officials Management
    Route::get('/officials', [OfficialController::class, 'index'])->name('officials.index');
    Route::post('/officials/update/{activeId?}', [OfficialController::class, 'update'])->name('officials.update');
    Route::get('/officials/history/{role}/{province?}', [OfficialController::class, 'history'])->name('officials.history');
    Route::post('/officials/reactivate/{id}', [OfficialController::class, 'reactivate'])->name('officials.reactivate');

    // ✅ Places Management (API routes)
    Route::get('/api/places/hierarchy', [PlaceController::class, 'getHierarchy'])->name('places.hierarchy');
    Route::get('/api/places/provinces', [PlaceController::class, 'getProvinces'])->name('places.provinces');
    Route::get('/api/places/municipalities', [PlaceController::class, 'getMunicipalities'])->name('places.municipalities');
    Route::get('/api/places/offices', [PlaceController::class, 'getOffices'])->name('places.offices');
    Route::post('/api/places/province', [PlaceController::class, 'storeProvince'])->name('places.province.store');
    Route::post('/api/places/municipality', [PlaceController::class, 'storeMunicipality'])->name('places.municipality.store');
    Route::post('/api/places/office', [PlaceController::class, 'storeOffice'])->name('places.office.store');
    Route::delete('/api/places/{id}', [PlaceController::class, 'destroy'])->name('places.destroy');

    // ✅ Repair Destinations Management
    Route::post('/repair-destinations', [RepairDestinationController::class, 'store'])->name('repair-destinations.store');
    Route::delete('/repair-destinations/{id}', [RepairDestinationController::class, 'destroy'])->name('repair-destinations.destroy');

    // ✅ Manual Management (Superadmin only)
    Route::post('/manual/upload', [ManualController::class, 'upload'])->name('manual.upload');
    Route::delete('/manual/delete', [ManualController::class, 'delete'])->name('manual.delete');
    Route::get('/manual/trashed', [ManualController::class, 'trashed'])->name('manual.trashed');
    Route::post('/manual/restore/{id}', [ManualController::class, 'restore'])->name('manual.restore');
    Route::delete('/manual/force-delete/{id}', [ManualController::class, 'forceDelete'])->name('manual.forceDelete');

    // � Test route to view soft-deleted items
    Route::get('/test/soft-deletes', function() {
        $destinations = \App\Models\RepairDestination::withTrashed()->get();
        $output = '<h1>All Repair Destinations (Including Soft Deleted)</h1>';
        $output .= '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
        $output .= '<tr><th>ID</th><th>Name</th><th>Deleted At</th><th>Status</th></tr>';
        foreach ($destinations as $dest) {
            $status = $dest->deleted_at ? '<span style="color:red;">SOFT DELETED ❌</span>' : '<span style="color:green;">ACTIVE ✅</span>';
            $deletedAt = $dest->deleted_at ? $dest->deleted_at->format('Y-m-d H:i:s') : 'NULL';
            $output .= "<tr><td>{$dest->id}</td><td>{$dest->name}</td><td>{$deletedAt}</td><td>{$status}</td></tr>";
        }
        $output .= '</table>';
        return $output;
    });

    // �📁 User Import Routes
    Route::post('/users/import', [UserController::class, 'importSimple'])->name('users.import');
    Route::post('/users/test-import', function() {
        return response('TEST ROUTE HIT! Time: ' . date('Y-m-d H:i:s'));
    })->name('users.test.import');
});

// Import progress endpoint - needs to be outside auth middleware for AJAX calls
Route::get('/import/progress', function () {
    $progress = \App\Models\ImportProgress::where('type', 'fets_import')->first();

    if (!$progress) {
        return response()->json([
            'total' => 0,
            'processed' => 0,
            'progress' => 0,
            'recent' => 'No import in progress',
            'logs' => []
        ]);
    }

    // Parse logs from JSON, return all logs for display
    $allLogs = json_decode($progress->logs ?? '[]', true) ?: [];

    return response()->json([
        'total' => $progress->total,
        'processed' => $progress->processed,
        'progress' => $progress->progress,
        'recent' => $progress->recent,
        'logs' => $allLogs,
        'updated' => json_decode($progress->updated ?? '[]', true) ?: [],
        'skipped' => json_decode($progress->skipped ?? '[]', true) ?: []
    ]);
});

Route::post('/import/clear-progress', function () {
    \App\Models\ImportProgress::where('type', 'fets_import')->delete();
    return response()->json(['success' => true, 'message' => 'Progress cleared']);
});

// TEST ROUTE - OUTSIDE MIDDLEWARE for debugging
Route::post('/users/test-import-debug', function() {
    return response('TEST ROUTE HIT OUTSIDE MIDDLEWARE! Time: ' . date('Y-m-d H:i:s'));
})->name('users.test.import.debug');

// Fetch eligible users for official roles (AJAX)
Route::middleware(['auth', 'role:superadmin'])->get('/eligible-users', function(Request $request){
    $role = $request->role;
    $province = $request->province;
    $activeId = $request->active_id;

    // Get all eligible users (including current official)
    $users = User::active()
        ->whereHas('roles', fn($q) => $q->where('name', $role))
        ->when($province && $province !== '-', fn($q) =>
            $q->whereRaw('LOWER(province) = ?', [strtolower($province)])
        )
        ->get(['id','fullname','province']);

    // Get the current official's user_id to mark it in the response
    $currentUserId = null;
    if ($activeId) {
        $currentOfficial = \App\Models\Official::find($activeId);
        if ($currentOfficial && $currentOfficial->user_id) {
            $currentUserId = $currentOfficial->user_id;
        }
    }

    return response()->json([
        'users' => $users,
        'current_user_id' => $currentUserId
    ]);
});


// 🟣 REGIONAL DPSC Routes
Route::middleware(['auth', 'role:Regional DPSC', 'verified', 'twofactor'])->group(function () {
    Route::get('/Regional/Dashboard', [App\Http\Controllers\DashboardController::class, 'regionalDashboard'])->name('Regional.Dashboard');
    Route::get('/Regional/VerifiedFETS', [FetsController::class, 'showVerifiedRegional'])->name('Regional.VerifiedFETS');
    Route::get('/Regional/ApprovedFETS', [FetsController::class, 'showForApproval'])->name('Regional.ApprovedFETS');
    Route::get('/Regional/MyInventory', [InventoryController::class, 'showMyInventory'])->name('Regional.MyInventory');
    Route::patch('/Regional/FETSrequest/{id}/approve', [FetsController::class, 'approve'])->name('fets.approve');
});

// 🟡 PROVINCIAL DPSC Routes
Route::middleware(['auth', 'role:Provincial DPSC', 'verified', 'twofactor'])->group(function () {
    Route::get('/Provincial/Dashboard', [App\Http\Controllers\DashboardController::class, 'provincialDashboard'])->name('Provincial.Dashboard');
    Route::get('/FETSrequest', [FetsController::class, 'reviewSubmitted'])->name('Provincial.FETSrequest');
    Route::patch('/FETSrequest/{id}/verify', [FetsController::class, 'verify'])->name('fets.verify');
    Route::patch('/FETSrequest/{id}/reject', [FetsController::class, 'reject'])->name('fets.reject');
    Route::get('/VerifiedFETS', [FetsController::class, 'showVerified'])->name('Provincial.VerifiedFETS');
    Route::get('/MyInventory', [InventoryController::class, 'showMyInventory'])->name('Provincial.MyInventory');
    Route::get('/ReturnFromRepair', [FetsController::class, 'showReturnFromRepair'])->name('Provincial.ReturnFromRepair');
});

// 🔄 RETURN FROM REPAIR FETS Routes (Employee submit & modal)
Route::middleware(['auth', 'verified', 'twofactor'])->group(function () {
    Route::get('/fets/return', [FetsController::class, 'showReturnFetsModal'])->name('fets.return.modal');
    Route::post('/fets/return/submit', [FetsController::class, 'submitReturnFets'])->name('fets.return.submit');
});

// 📦 Inventory Management (All Roles)
Route::middleware(['auth', 'verified', 'twofactor'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Show Upload Form (Blade)
    Route::get('/inventory/upload', [InventoryController::class, 'showUploadForm'])->name('inventory.upload');

    // Multi-file Upload (AJAX + progress)
    Route::post('/inventory/upload', [InventoryController::class, 'upload'])->name('inventory.upload.submit');

    // 📖 View Manual (All authenticated users)
    Route::get('/manual', [ManualController::class, 'view'])->name('manual.view');

    // Export full inventory
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');

    // Export by receiver/file
    Route::get('/inventory/export/{receiver}', [InventoryController::class, 'exportByReceiver'])
        ->name('inventory.export.byfile');

    // Optional: Clear inventory (high verification inside controller)
    Route::delete('/inventory/clear', [InventoryController::class, 'clearInventory'])
        ->name('inventory.clearinventory');
});


// Employee Inventory View
Route::get('/Inventory', [InventoryController::class, 'showEmployeeInventory'])
    ->middleware(['auth', 'role:Employee', 'verified', 'twofactor'])
    ->name('Inventory');

// Unserviceable Units View (Head of Property only)
Route::get('/unserviceable-units', [InventoryController::class, 'showUnserviceableUnits'])
    ->middleware(['auth', 'role:Employee', 'verified', 'twofactor'])
    ->name('unserviceable.units');

// 📄 FETS File Access Routes
Route::get('/SubmittedFETS', [FetsController::class, 'submittedFets'])
    ->middleware(['auth', 'role:Employee', 'verified', 'twofactor'])
    ->name('SubmittedFETS');

Route::get('/fets/download/{id}', [FetsController::class, 'download'])
    ->middleware(['auth', 'verified', 'twofactor'])
    ->name('fets.download');

Route::get('/fets/preview/{id}', [FetsController::class, 'preview'])
    ->middleware(['auth', 'verified', 'twofactor'])
    ->name('fets.preview');

Route::get('/fets/view/{id}', [FetsController::class, 'preview'])->name('fets.view');

// FETS Embed on DPSC's
Route::middleware(['auth','verified','twofactor'])->group(function () {
    Route::get('/FETS/embed', [FetsController::class, 'selectEmbed'])->name('fets.select.embed');
    Route::get('/SubmittedFETS/embed', [FetsController::class, 'submittedEmbed'])->name('fets.submitted.embed');
});

Route::get('/fets/submittedEmbed', [FetsController::class, 'submittedEmbed'])
    ->middleware(['auth', 'verified', 'twofactor'])
    ->name('fets.submittedEmbed');

// Block direct GET access to /users/import
Route::get('/users/import', function () {
    return redirect()->route('users')
        ->with('error', 'You cannot access /users/import directly. Please upload a CSV.');
});

require __DIR__ . '/auth.php';
