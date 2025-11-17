<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Repair Destinations (Including Soft Deleted) ===\n\n";

// Get all including trashed
$all = \App\Models\RepairDestination::withTrashed()->get();

echo "Total: {$all->count()}\n\n";

foreach ($all as $item) {
    $status = $item->deleted_at ? "❌ DELETED ({$item->deleted_at})" : "✓ Active";
    echo "ID: {$item->id} | Name: {$item->name} | Status: {$status}\n";
}

echo "\n=== Active Only ===\n";
$active = \App\Models\RepairDestination::all();
echo "Total Active: {$active->count()}\n";
foreach ($active as $item) {
    echo "- {$item->id}: {$item->name}\n";
}

echo "\n=== Soft Deleted Only ===\n";
$deleted = \App\Models\RepairDestination::onlyTrashed()->get();
echo "Total Deleted: {$deleted->count()}\n";
foreach ($deleted as $item) {
    echo "- {$item->id}: {$item->name} (deleted: {$item->deleted_at})\n";
}
