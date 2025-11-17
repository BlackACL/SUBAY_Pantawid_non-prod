<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTIC REPORT ===\n\n";

// 0. Check fets_documents table structure
echo "0. Checking fets_documents columns:\n";
$columns = DB::select("SHOW COLUMNS FROM fets_documents");
$columnNames = array_map(function($col) { return $col->Field; }, $columns);
echo "Columns: " . implode(', ', $columnNames) . "\n\n";
echo "Has verified_by? " . (in_array('verified_by', $columnNames) ? 'YES' : 'NO') . "\n\n";

// 1. Check ALL repair FETS (any status)
echo "1. ALL 'For Repair' FETS Documents:\n";
$repairFets = DB::table('fets_documents')
    ->where('transfer_movement', 'For Repair')
    ->get(['id', 'user_id', 'verified_by', 'approved_by', 'status', 'transfer_movement', 'to_receiver', 'repair_destination']);

foreach ($repairFets as $fets) {
    echo "  FETS #{$fets->id}\n";
    echo "    Status: {$fets->status}\n";
    echo "    Repair Destination: {$fets->repair_destination}\n";
    
    // Get submitter
    $submitter = DB::table('users')->where('id', $fets->user_id)->first(['fullname', 'province']);
    if ($submitter) {
        echo "    Submitter: {$submitter->fullname} (Province: {$submitter->province})\n";
    }
    
    // Get verifier
    if ($fets->verified_by) {
        $verifier = DB::table('users')->where('id', $fets->verified_by)->first(['fullname', 'province', 'access_level']);
        if ($verifier) {
            echo "    Verified by: {$verifier->fullname} (Province: {$verifier->province}, Role: {$verifier->access_level})\n";
        }
    } else {
        echo "    Verified by: NULL\n";
    }
    
    // Get approver
    if ($fets->approved_by) {
        $approver = DB::table('users')->where('id', $fets->approved_by)->first(['fullname', 'province', 'access_level']);
        if ($approver) {
            echo "    Approved by: {$approver->fullname} (Province: {$approver->province}, Role: {$approver->access_level})\n";
        }
    } else {
        echo "    Approved by: NULL\n";
    }
    
    // Check fets_items
    $items = DB::table('fets_items')
        ->where('fets_document_id', $fets->id)
        ->get(['property_no', 'item_status']);
    
    echo "    Items in fets_items: " . count($items) . "\n";
    foreach ($items as $item) {
        echo "      - {$item->property_no} (Status: {$item->item_status})\n";
    }
    
    // Check inventory status
    if (count($items) > 0) {
        $propNos = array_column($items->toArray(), 'property_no');
        $invItems = DB::table('inventory')
            ->whereIn('PROPERTY_NO', $propNos)
            ->get(['PROPERTY_NO', 'STATUS', 'DPO_REMARKS']);
        
        echo "    Inventory Status:\n";
        foreach ($invItems as $inv) {
            echo "      - {$inv->PROPERTY_NO}: {$inv->STATUS} | {$inv->DPO_REMARKS}\n";
        }
    }
    
    echo "\n";
}

// 2. Check what the Return from Repair query should find
echo "\n2. What Provincial DPSC should see:\n";
$user = DB::table('users')->where('access_level', 'Provincial DPSC')->where('province', 'Davao de Oro')->first();
if ($user) {
    echo "User: {$user->fullname} (Province: {$user->province})\n\n";
    
    // Use the NEW logic - filter by who verified it
    $fetsForRepair = DB::table('fets_documents')
        ->where('status', 'approved')
        ->where('transfer_movement', 'For Repair')
        ->where('verified_by', $user->id)
        ->get(['id']);
    
    echo "FETS IDs verified by this user: " . implode(', ', $fetsForRepair->pluck('id')->toArray()) . "\n\n";
    
    $fetsIds = $fetsForRepair->pluck('id')->toArray();
    $propNos = DB::table('fets_items')
        ->whereIn('fets_document_id', $fetsIds)
        ->pluck('property_no')
        ->unique()
        ->values()
        ->toArray();
    
    echo "Property numbers from fets_items: " . implode(', ', $propNos) . "\n";
    echo "Count: " . count($propNos) . "\n\n";
    
    if (count($propNos) > 0) {
        echo "Checking inventory for these property numbers:\n";
        $items = DB::table('inventory')
            ->whereIn('PROPERTY_NO', $propNos)
            ->get(['PROPERTY_NO', 'STATUS', 'DPO_REMARKS', 'RECEIVER']);
        
        foreach ($items as $item) {
            echo "  {$item->PROPERTY_NO}:\n";
            echo "    Status: {$item->STATUS}\n";
            echo "    Remarks: {$item->DPO_REMARKS}\n";
            echo "    Receiver: {$item->RECEIVER}\n";
        }
    }
}

echo "\n=== END REPORT ===\n";
