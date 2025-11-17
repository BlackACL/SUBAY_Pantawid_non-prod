<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== INVENTORY STATUS CHECK ===\n\n";

$propNo = 'FO11-HE3-21-0062';

$item = DB::table('inventory')
    ->where('PROPERTY_NO', $propNo)
    ->first(['PROPERTY_NO', 'STATUS', 'DPO_REMARKS', 'RECEIVER']);

if ($item) {
    echo "Property: {$item->PROPERTY_NO}\n";
    echo "Status: " . ($item->STATUS ?: 'NULL') . "\n";
    echo "Remarks: {$item->DPO_REMARKS}\n";
    echo "Receiver: {$item->RECEIVER}\n\n";
} else {
    echo "Item not found!\n\n";
}

echo "FETS History:\n";
$fetsItems = DB::table('fets_items as fi')
    ->join('fets_documents as fd', 'fi.fets_document_id', '=', 'fd.id')
    ->where('fi.property_no', $propNo)
    ->orderBy('fd.created_at', 'desc')
    ->get(['fd.id', 'fd.transfer_movement', 'fd.status', 'fd.to_receiver']);

foreach ($fetsItems as $fets) {
    echo "  FETS #{$fets->id}: {$fets->transfer_movement} - Status: {$fets->status}\n";
    echo "    To: {$fets->to_receiver}\n";
}
