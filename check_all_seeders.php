<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING ALL SEEDERS AGAINST DATABASE ===\n\n";

// 1. Check Roles
echo "1. ROLES TABLE:\n";
echo "   Columns: ";
$roleColumns = DB::select("DESCRIBE roles");
foreach ($roleColumns as $col) {
    echo $col->Field . " (" . $col->Type . "), ";
}
echo "\n   Current Data:\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "   - ID: {$role->id}, Name: {$role->name}\n";
}

// 2. Check Repair Destinations
echo "\n2. REPAIR_DESTINATIONS TABLE:\n";
echo "   Columns: ";
$repairColumns = DB::select("DESCRIBE repair_destinations");
foreach ($repairColumns as $col) {
    echo $col->Field . " (" . $col->Type . "), ";
}
echo "\n   Current Data:\n";
$repairs = DB::table('repair_destinations')->get();
foreach ($repairs as $repair) {
    echo "   - ID: {$repair->id}, Name: {$repair->name}\n";
}

// 3. Check Officials
echo "\n3. OFFICIALS TABLE:\n";
echo "   Columns: ";
$officialsColumns = DB::select("DESCRIBE officials");
foreach ($officialsColumns as $col) {
    echo $col->Field . " (" . $col->Type . "), ";
}
echo "\n   Sample Data (first 5):\n";
$officials = DB::table('officials')->limit(5)->get();
foreach ($officials as $official) {
    $data = json_encode((array)$official);
    echo "   - " . substr($data, 0, 100) . "...\n";
}
echo "   Total Officials: " . DB::table('officials')->count() . "\n";

// 4. Check Users (Superadmin)
echo "\n4. USERS TABLE (checking for superadmin):\n";
echo "   Columns: ";
$usersColumns = DB::select("DESCRIBE users");
foreach ($usersColumns as $col) {
    echo $col->Field . " (" . $col->Type . "), ";
}
echo "\n   Superadmin User:\n";
$superadmin = DB::table('users')->where('email', 'main.central321@gmail.com')->first();
if ($superadmin) {
    echo "   - Found: Email={$superadmin->email}, Username={$superadmin->username}\n";
    echo "   - Fullname field exists: " . (property_exists($superadmin, 'fullname') ? 'YES (value: '.$superadmin->fullname.')' : 'NO') . "\n";
    echo "   - Full_name field exists: " . (property_exists($superadmin, 'full_name') ? 'YES' : 'NO') . "\n";
} else {
    echo "   - NOT FOUND\n";
}

echo "\n=== CHECK COMPLETE ===\n";
