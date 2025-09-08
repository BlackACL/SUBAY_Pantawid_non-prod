<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FetsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorCodeController;
use App\Http\Controllers\OfficialController;
use App\Models\ImportProgress;

// Redirect root to login
Route::get('/', fn () => redirect('login'));
   
// General Authenticated Routes
Route::middleware('auth')->group(function () {
    // 2FA Routes
    Route::get('/verify', [TwoFactorCodeController::class, 'verify'])->name('verify');
    Route::post('/verify', [TwoFactorCodeController::class, 'process'])->name('verify.process');
    Route::post('/verify/resend', [TwoFactorCodeController::class, 'resend'])->name('verify.resend');

    // Password Update
    Route::get('/UpdatePassword', [ProfileController::class, 'edit'])->name('password.edit');
    Route::patch('/UpdatePassword', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    // FETS Selection/Generation (All Roles)
    Route::middleware(['verified', 'twofactor'])->group(function () {
        Route::get('/FETS', [FetsController::class, 'select'])->name('fets.select');
        Route::post('/FETS/generate', [FetsController::class, 'generate'])->name('fets.generate');
    });
});

// 🔐 SUPERADMIN Routes
Route::middleware(['auth', 'role:superadmin', 'verified', 'twofactor'])->group(function () {
    // Existing SuperAdmin routes
    Route::get('/logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('superadmin.logs_nav.logs');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users/addusers', [UserController::class, 'store'])->name('addusers.store');
    Route::get('/users/{user}/profile', [UserController::class, 'showProfile'])->name('users.profile');
    Route::get('/archives', [UserController::class, 'archives'])->name('archives');
    Route::post('/users/{user}/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::post('/users/{user}/unarchive', [UserController::class, 'unarchive'])->name('users.unarchive');

    // ✅ Officials Management
    Route::get('/officials', [OfficialController::class, 'index'])->name('officials.index');
    Route::post('/officials/update/{activeId?}', [OfficialController::class, 'update'])->name('officials.update');
    Route::get('/officials/history/{role}/{province?}', [OfficialController::class, 'history'])->name('officials.history');

    // 🔹 Reactivate Historical Official
    Route::post('/officials/reactivate/{id}', [OfficialController::class, 'reactivate'])->name('officials.reactivate');
});

// 🟣 REGIONAL DPSC Routes
Route::middleware(['auth', 'role:Regional DPSC', 'verified', 'twofactor'])->group(function () {
    Route::get('/Regional/VerifiedFETS', [FetsController::class, 'showVerifiedRegional'])->name('Regional.VerifiedFETS');
    Route::get('/Regional/ApprovedFETS', [FetsController::class, 'showForApproval'])->name('Regional.ApprovedFETS');
    Route::get('/Regional/MyInventory', [InventoryController::class, 'showMyInventory'])->name('Regional.MyInventory');
    Route::patch('/Regional/FETSrequest/{id}/approve', [FetsController::class, 'approve'])->name('fets.approve');
});

// 🟡 PROVINCIAL DPSC Routes
Route::middleware(['auth', 'role:Provincial DPSC', 'verified', 'twofactor'])->group(function () {
    Route::get('/FETSrequest', [FetsController::class, 'reviewSubmitted'])->name('Provincial.FETSrequest');
    Route::patch('/FETSrequest/{id}/verify', [FetsController::class, 'verify'])->name('fets.verify');
    Route::patch('/FETSrequest/{id}/reject', [FetsController::class, 'reject'])->name('fets.reject');
    Route::get('/VerifiedFETS', [FetsController::class, 'showVerified'])->name('Provincial.VerifiedFETS');
    Route::get('/MyInventory', [InventoryController::class, 'showMyInventory'])->name('Provincial.MyInventory');
});

// 📦 Inventory Management (All Roles)
Route::middleware(['auth', 'verified', 'twofactor'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/upload', [InventoryController::class, 'showUploadForm'])->name('inventory.upload');
    Route::post('/inventory/upload', [InventoryController::class, 'upload'])->name('inventory.upload.submit');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
});

// Employee Inventory View
Route::get('/Inventory', [InventoryController::class, 'showEmployeeInventory'])
    ->middleware(['auth', 'role:Employee', 'verified', 'twofactor'])
    ->name('Inventory');

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

// Handle CSV upload/import
Route::post('/users/import', [UserController::class, 'import'])->name('users.import');

Route::get('/import/progress', function () {
    return \App\Models\ImportProgress::where('type', 'fets_import')->first();
});

require __DIR__ . '/auth.php';
