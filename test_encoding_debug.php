<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Get the user from database
$user = User::where('email', 'davaodelnorteemployee@gmail.com')->first();

if ($user) {
    echo "Database fullname: " . $user->fullname . "\n";
    echo "Hex bytes: " . bin2hex($user->fullname) . "\n\n";
    
    // Simulate what CSV would have
    $csvName = "MEJORADA , DIANA OPEÃ'A";
    echo "CSV name: " . $csvName . "\n";
    echo "Hex bytes: " . bin2hex($csvName) . "\n\n";
    
    // Test the fix function
    $fixEncoding = function($text) {
        if (!is_string($text)) return $text;
        $text = str_replace("Ã'", "Ñ", $text);
        $text = str_replace("Ã±", "ñ", $text);
        return $text;
    };
    
    $fixed = $fixEncoding($csvName);
    echo "After fix: " . $fixed . "\n";
    echo "Hex bytes: " . bin2hex($fixed) . "\n\n";
    
    echo "Match? " . ($user->fullname === $fixed ? "YES" : "NO") . "\n";
} else {
    echo "User not found!\n";
}
