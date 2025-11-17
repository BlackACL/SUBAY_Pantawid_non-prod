<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking FETS items data...\n\n";

// Find FETS with no items in junction table
$fetsWithoutItems = DB::table('fets_documents as f')
    ->leftJoin('fets_items as fi', 'f.id', '=', 'fi.fets_document_id')
    ->whereNull('fi.id')
    ->where('f.transfer_movement', 'For Repair')
    ->whereIn('f.status', ['submitted', 'verified', 'approved'])
    ->select('f.id', 'f.transfer_movement', 'f.status', 'f.to_receiver', 'f.user_id')
    ->get();

echo "Found " . count($fetsWithoutItems) . " FETS documents without items in junction table\n\n";

foreach ($fetsWithoutItems as $fets) {
    echo "FETS #{$fets->id} - Status: {$fets->status}, Movement: {$fets->transfer_movement}\n";
    echo "  Receiver: {$fets->to_receiver}\n";
    
    // Get the submitter's info
    $submitter = DB::table('users')->where('id', $fets->user_id)->first();
    if ($submitter) {
        echo "  Submitter: {$submitter->fullname} (Province: {$submitter->province})\n";
        
        // Find inventory items that might belong to this FETS
        // Look for items that are "Being Assessed for Repair" and belong to users in the same province
        $repairItems = DB::table('inventory')
            ->where('STATUS', 'Being Assessed for Repair')
            ->where('DPO_REMARKS', 'like', 'Assigned for Repair to:%')
            ->whereIn('RECEIVER', DB::table('users')
                ->where('province', $submitter->province)
                ->pluck('fullname'))
            ->get(['PROPERTY_NO', 'RECEIVER', 'STATUS', 'DPO_REMARKS']);
        
        echo "  Found " . count($repairItems) . " potential items:\n";
        foreach ($repairItems as $item) {
            echo "    - {$item->PROPERTY_NO} (Owner: {$item->RECEIVER})\n";
            echo "      Status: {$item->STATUS}\n";
            echo "      Remarks: {$item->DPO_REMARKS}\n";
        }
    }
    echo "\n";
}

echo "\nDo you want to populate fets_items table with these items? (yes/no): ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != 'yes'){
    echo "Aborted.\n";
    exit;
}

// Populate the table
foreach ($fetsWithoutItems as $fets) {
    $submitter = DB::table('users')->where('id', $fets->user_id)->first();
    if ($submitter) {
        $repairItems = DB::table('inventory')
            ->where('STATUS', 'Being Assessed for Repair')
            ->where('DPO_REMARKS', 'like', 'Assigned for Repair to:%')
            ->whereIn('RECEIVER', DB::table('users')
                ->where('province', $submitter->province)
                ->pluck('fullname'))
            ->get(['PROPERTY_NO']);
        
        foreach ($repairItems as $item) {
            DB::table('fets_items')->insert([
                'fets_document_id' => $fets->id,
                'property_no' => $item->PROPERTY_NO,
                'item_status' => $fets->status,
                'item_remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "  ✓ Added {$item->PROPERTY_NO} to FETS #{$fets->id}\n";
        }
    }
}

echo "\nDone!\n";
