<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Provincial\FetsVerifyController;
use App\Http\Controllers\FetsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorCodeController;

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
        Route::get('/FETS', [InventoryController::class, 'select'])->name('fets.select');
        Route::post('/FETS/generate', [FetsController::class, 'generate'])->name('fets.generate');
    });
});

// SUPERADMIN Routes
Route::middleware(['auth', 'role:superadmin', 'verified', 'twofactor'])->group(function () {
    Route::get('/logs', fn () => view('superadmin.logs_nav.logs'))->name('logs');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users/addusers', [UserController::class, 'store'])->name('addusers.store');
    Route::get('/users/{user}/profile', [UserController::class, 'showProfile'])->name('users.profile');
    Route::get('/archives', [UserController::class, 'archives'])->name('archives');
    Route::post('/users/{user}/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::post('/users/{user}/unarchive', [UserController::class, 'unarchive'])->name('users.unarchive');
});

// REGIONAL DPSC Routes
Route::middleware(['auth', 'role:Regional DPSC', 'verified', 'twofactor'])->group(function () {
    Route::get('/Regional/VerifiedFETS', [FetsController::class, 'showVerifiedRegional'])->name('Regional.VerifiedFETS'); // ✅ FIXED
    Route::get('/Regional/ApprovedFETS', [FetsController::class, 'showForApproval'])->name('Regional.ApprovedFETS');
    Route::get('/Regional/MyInventory', [InventoryController::class, 'showMyInventory'])->name('Regional.MyInventory');
    Route::patch('/Regional/FETSrequest/{id}/approve', [FetsController::class, 'approve'])->name('fets.approve');
});

// PROVINCIAL DPSC Routes
Route::middleware(['auth', 'role:Provincial DPSC', 'verified', 'twofactor'])->group(function () {
    Route::get('/FETSrequest', [FetsController::class, 'reviewSubmitted'])->name('Provincial.FETSrequest');
    Route::patch('/FETSrequest/{id}/verify', [FetsController::class, 'verify'])->name('fets.verify');
    Route::patch('/FETSrequest/{id}/reject', [FetsController::class, 'reject'])->name('fets.reject');
    Route::get('/VerifiedFETS', [FetsController::class, 'showVerified'])->name('Provincial.VerifiedFETS');
    Route::get('/MyInventory', [InventoryController::class, 'showMyInventory'])->name('Provincial.MyInventory');
});

// Inventory Management (All Roles)
Route::middleware(['auth', 'verified', 'twofactor'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    // Show the form to upload a new CSV file containing inventory data
    Route::get('/inventory/upload', [InventoryController::class, 'showUploadForm'])->name('inventory.upload');
    // Process the uploaded CSV file and store the inventory data in the database
    Route::post('/inventory/upload', [InventoryController::class, 'upload'])->name('inventory.upload.submit');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
});


// Employee Inventory View
Route::get('/Inventory', [InventoryController::class, 'showEmployeeInventory'])
    ->middleware(['auth', 'role:Employee', 'verified', 'twofactor'])
    ->name('Inventory');;

Route::get('/provincial/fets/verify/{id}', [FetsVerifyController::class, 'show'])
    ->name('provincial.fets.verify')
    ->middleware('auth'); // add role-based middleware if needed

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



require __DIR__ . '/auth.php';
