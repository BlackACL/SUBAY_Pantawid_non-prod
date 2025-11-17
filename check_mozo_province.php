<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\FetsDocument;
use Illuminate\Support\Facades\DB;

echo "=== Checking MOZO's Details ===\n";

$mozo = User::where('fullname', 'like', '%MOZO%')->first();
if ($mozo) {
    echo "ID: {$mozo->id}\n";
    echo "Name: {$mozo->fullname}\n";
    echo "Access Level: {$mozo->access_level}\n";
    echo "Province: {$mozo->province}\n\n";
    
    echo "=== MOZO's Submitted FETS ===\n";
    $fetsList = FetsDocument::where('submitted_by', $mozo->id)
        ->where('status', 'submitted')
        ->get();
    
    foreach ($fetsList as $fets) {
        echo "FETS #{$fets->id}\n";
        echo "  Transfer Movement: {$fets->transfer_movement}\n";
        echo "  Status: {$fets->status}\n";
        echo "  Submitted By: {$mozo->fullname} (Province: {$mozo->province})\n";
        
        // Get property numbers
        $propNos = DB::table('fets_items')
            ->where('fets_document_id', $fets->id)
            ->pluck('property_no')
            ->toArray();
        echo "  Property Numbers: " . implode(', ', $propNos) . "\n\n";
    }
} else {
    echo "MOZO not found\n";
}

echo "\n=== Checking MUNDIZ's Details ===\n";
$mundiz = User::where('fullname', 'like', '%MUNDIZ%')->first();
if ($mundiz) {
    echo "ID: {$mundiz->id}\n";
    echo "Name: {$mundiz->fullname}\n";
    echo "Access Level: {$mundiz->access_level}\n";
    echo "Province: {$mundiz->province}\n";
}
