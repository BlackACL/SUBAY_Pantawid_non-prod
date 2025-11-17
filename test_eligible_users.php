<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Eligible Users Query ===\n\n";

// Test for Davao De Oro Provincial DPSC
$role = "Provincial DPSC";
$province = "Davao De Oro";

echo "Role: $role\n";
echo "Province: $province\n\n";

// Get the current active official for Davao De Oro
$currentOfficial = \App\Models\Official::where('role', $role)
    ->where('province', $province)
    ->where('active', true)
    ->first();

if ($currentOfficial) {
    echo "Current Active Official:\n";
    echo "  ID: {$currentOfficial->id}\n";
    echo "  Name: {$currentOfficial->fullname}\n";
    echo "  User ID: " . ($currentOfficial->user_id ?? 'NULL') . "\n";
    if ($currentOfficial->user) {
        echo "  User Full Name: {$currentOfficial->user->fullname}\n";
    }
    echo "\n";
}

// Get all users with Provincial DPSC role and Davao De Oro province
echo "=== All Users with this role/province ===\n";
$allUsers = \App\Models\User::active()
    ->whereHas('roles', fn($q) => $q->where('name', $role))
    ->whereRaw('LOWER(province) = ?', [strtolower($province)])
    ->get(['id','fullname','province']);

echo "Total: {$allUsers->count()}\n";
foreach ($allUsers as $user) {
    $isCurrent = ($currentOfficial && $currentOfficial->user_id == $user->id) ? " ← CURRENT" : "";
    echo "  - ID: {$user->id} | {$user->fullname} | {$user->province}{$isCurrent}\n";
}

// Get eligible users (excluding current)
echo "\n=== Eligible Users (excluding current) ===\n";
$eligibleUsers = \App\Models\User::active()
    ->whereHas('roles', fn($q) => $q->where('name', $role))
    ->whereRaw('LOWER(province) = ?', [strtolower($province)])
    ->when($currentOfficial && $currentOfficial->user_id, function($q) use ($currentOfficial) {
        $q->where('id', '!=', $currentOfficial->user_id);
    })
    ->get(['id','fullname','province']);

echo "Total: {$eligibleUsers->count()}\n";
foreach ($eligibleUsers as $user) {
    echo "  - ID: {$user->id} | {$user->fullname} | {$user->province}\n";
}
