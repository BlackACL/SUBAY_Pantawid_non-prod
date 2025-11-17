<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Regional DPSC Official Info ===\n\n";

$regionalOfficial = \App\Models\Official::where('role', 'Regional DPSC')
    ->where('active', true)
    ->with('user')
    ->first();

if ($regionalOfficial) {
    echo "Official ID: {$regionalOfficial->id}\n";
    echo "Full Name: {$regionalOfficial->fullname}\n";
    echo "User ID: " . ($regionalOfficial->user_id ?? 'NULL') . "\n";
    
    if ($regionalOfficial->user) {
        echo "\nUser Info:\n";
        echo "  Full Name: {$regionalOfficial->user->fullname}\n";
        echo "  Region: " . ($regionalOfficial->user->region ?? 'NULL') . "\n";
        echo "  Province: " . ($regionalOfficial->user->province ?? 'NULL') . "\n";
        echo "  Office: " . ($regionalOfficial->user->office ?? 'NULL') . "\n";
    } else {
        echo "\nNo user relationship found!\n";
    }
} else {
    echo "No active Regional DPSC found.\n";
}
