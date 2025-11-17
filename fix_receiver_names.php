<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Fixing Inventory RECEIVER Names ===\n\n";

// Update inventory records from "Al Jay Meliton" to "Meliton , Al Jay"
$updated = DB::table('inventory')
    ->where('RECEIVER', 'Al Jay Meliton')
    ->update(['RECEIVER' => 'Meliton , Al Jay', 'updated_at' => now()]);

echo "✅ Updated {$updated} inventory records\n";
echo "   Changed RECEIVER from 'Al Jay Meliton' to 'Meliton , Al Jay'\n\n";

// Verify
$items = DB::table('inventory')
    ->where('RECEIVER', 'Meliton , Al Jay')
    ->get();

echo "Items now assigned to 'Meliton , Al Jay': {$items->count()}\n";
foreach ($items as $item) {
    echo "  - {$item->PROPERTY_NO}: STATUS=" . ($item->STATUS ?? 'NULL') . "\n";
}
