<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Official;
use App\Models\User;

echo "=== Linking Head of Property to User ===\n\n";

// Find the Head of Property official
$headOfProperty = Official::where('role', 'Head of Property')
    ->where('active', true)
    ->first();

if (!$headOfProperty) {
    echo "No active Head of Property found!\n";
    exit;
}

echo "Head of Property: {$headOfProperty->fullname}\n";
echo "Current user_id: " . ($headOfProperty->user_id ?? 'NULL') . "\n\n";

// Find the user by fullname
$user = User::where('fullname', 'like', '%Al Jay%')->first();

if (!$user) {
    echo "User 'Al Jay Meliton' not found!\n";
    exit;
}

echo "Found user:\n";
echo "  ID: {$user->id}\n";
echo "  Fullname: {$user->fullname}\n";
echo "  Access Level: {$user->access_level}\n\n";

// Link them - use update to bypass observer or set changed_by manually
\Illuminate\Support\Facades\DB::table('officials')
    ->where('id', $headOfProperty->id)
    ->update(['user_id' => $user->id, 'updated_at' => now()]);

echo "✅ Successfully linked Head of Property to user ID {$user->id}\n";
