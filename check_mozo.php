<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Searching for Mozo user ===\n\n";

$user = \App\Models\User::where('fullname', 'LIKE', '%Mozo%')->first();

if ($user) {
    echo "User found!\n";
    echo "ID: {$user->id}\n";
    echo "Full Name: {$user->fullname}\n";
    echo "Region: " . ($user->region ?? 'NULL') . "\n";
    echo "Province: " . ($user->province ?? 'NULL') . "\n";
    echo "Office: " . ($user->office ?? 'NULL') . "\n";
    
    // Now update the Regional DPSC official to link to this user
    $regionalOfficial = \App\Models\Official::where('role', 'Regional DPSC')
        ->where('active', true)
        ->first();
    
    if ($regionalOfficial) {
        echo "\n=== Regional DPSC Official ===\n";
        echo "ID: {$regionalOfficial->id}\n";
        echo "Name: {$regionalOfficial->fullname}\n";
        echo "Current User ID: " . ($regionalOfficial->user_id ?? 'NULL') . "\n";
    }
} else {
    echo "No user found with name containing 'Mozo'\n";
}
