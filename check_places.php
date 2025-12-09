<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CURRENT DATABASE DATA ===\n\n";

echo "PROVINCES:\n";
$provinces = DB::table('places')->where('type', 'province')->orderBy('name')->get(['id', 'name', 'parent_id']);
foreach ($provinces as $p) {
    echo "  - {$p->name} (ID: {$p->id})\n";
}

echo "\nMUNICIPALITIES (first 10):\n";
$municipalities = DB::table('places')->where('type', 'municipality')->orderBy('name')->limit(10)->get(['id', 'name', 'parent_id']);
foreach ($municipalities as $m) {
    echo "  - {$m->name} (ID: {$m->id}, Parent: {$m->parent_id})\n";
}

echo "\nOFFICES (first 10):\n";
$offices = DB::table('places')->where('type', 'office')->orderBy('name')->limit(10)->get(['id', 'name', 'parent_id']);
foreach ($offices as $o) {
    echo "  - {$o->name} (ID: {$o->id}, Parent: {$o->parent_id})\n";
}

echo "\n=== COUNTS ===\n";
echo "Total Provinces: " . DB::table('places')->where('type', 'province')->count() . "\n";
echo "Total Municipalities: " . DB::table('places')->where('type', 'municipality')->count() . "\n";
echo "Total Offices: " . DB::table('places')->where('type', 'office')->count() . "\n";
