<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FetsDocument;
use App\Models\Official;
use Illuminate\Support\Facades\DB;

echo "=== Checking Unserviceable FETS ===\n\n";

// Check for Return to Lender FETS with Unserviceable remarks
$unserviceableFets = FetsDocument::where('transfer_movement', 'Return to Lender')
    ->where('remarks', 'Unserviceable')
    ->with('submitter')
    ->get();

echo "Found " . $unserviceableFets->count() . " unserviceable FETS\n\n";

foreach ($unserviceableFets as $fets) {
    echo "FETS #{$fets->id}\n";
    echo "  Status: {$fets->status}\n";
    echo "  Submitted by: {$fets->submitter->fullname}\n";
    echo "  To Receiver: {$fets->to_receiver}\n";
    echo "  Remarks: {$fets->remarks}\n";
    
    // Get property numbers
    $propNos = DB::table('fets_items')
        ->where('fets_document_id', $fets->id)
        ->pluck('property_no')
        ->toArray();
    echo "  Property Numbers: " . implode(', ', $propNos) . "\n";
    
    // Check inventory status
    foreach ($propNos as $propNo) {
        $item = DB::table('inventory')->where('PROPERTY_NO', $propNo)->first();
        if ($item) {
            echo "    - {$propNo}:\n";
            echo "      RECEIVER: {$item->RECEIVER}\n";
            echo "      STATUS: " . ($item->STATUS ?? 'NULL') . "\n";
            echo "      DPO_REMARKS: " . ($item->DPO_REMARKS ?? 'NULL') . "\n";
        }
    }
    echo "\n";
}

echo "\n=== Checking Head of Property ===\n";
$headOfProperty = Official::where('role', 'Head of Property')
    ->where('active', true)
    ->first();

if ($headOfProperty) {
    echo "Active Head of Property: {$headOfProperty->fullname}\n";
    echo "User ID: {$headOfProperty->user_id}\n";
    
    if ($headOfProperty->user_id) {
        $user = \App\Models\User::find($headOfProperty->user_id);
        if ($user) {
            echo "User Fullname: {$user->fullname}\n";
            echo "User Access Level: {$user->access_level}\n";
        }
    }
} else {
    echo "No active Head of Property found\n";
}

echo "\n=== Checking Inventory for Head of Property ===\n";
if ($headOfProperty && $headOfProperty->user_id) {
    $user = \App\Models\User::find($headOfProperty->user_id);
    if ($user) {
        $items = DB::table('inventory')
            ->where('RECEIVER', $user->fullname)
            ->get();
        echo "Items assigned to {$user->fullname}: " . $items->count() . "\n";
        
        foreach ($items as $item) {
            echo "  - {$item->PROPERTY_NO}: STATUS=" . ($item->STATUS ?? 'NULL') . "\n";
        }
    }
}
