<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Repair Destination...\n\n";

// Check table structure
echo "=== Table Structure ===\n";
$columns = DB::select('DESCRIBE repair_destinations');
foreach ($columns as $col) {
    echo "{$col->Field} | {$col->Type} | Null: {$col->Null} | Key: {$col->Key} | Default: {$col->Default}\n";
}

echo "\n=== Attempting to Create ===\n";
try {
    $dest = \App\Models\RepairDestination::create(['name' => 'Test Destination ' . time()]);
    echo "✓ Successfully created: {$dest->name}\n";
    echo "✓ ID: {$dest->id}\n";
    
    // Delete the test record
    $dest->delete();
    echo "✓ Test record deleted\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    echo "✗ Trace: {$e->getTraceAsString()}\n";
}

echo "\n=== Current Records ===\n";
$all = \App\Models\RepairDestination::all();
echo "Total: {$all->count()}\n";
foreach ($all as $item) {
    echo "- {$item->id}: {$item->name}\n";
}
