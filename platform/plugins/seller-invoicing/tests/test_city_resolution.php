<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../src/Supports/LocationResolver.php';

use SparroWave\SellerInvoicing\Supports\LocationResolver;

echo "====================================================\n";
echo "Testing City Resolution in LocationResolver\n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$description}\n";
        if ($detail) {
            echo "        {$detail}\n";
        }
        $passed++;
    } else {
        echo " [FAIL] {$description}\n";
        if ($detail) {
            echo "        Details: {$detail}\n";
        }
        $failed++;
    }
}

// TEST 1: Numeric ID '13' without DB (fallback or with DB)
// If DB is connected, '13' -> 'Chittorgarh'
// If no DB in unit test, it returns '13' safely without crashing
// If Laravel bootstrap exists, test full DB lookup
if (file_exists(__DIR__ . '/../../../../bootstrap/app.php')) {
    try {
        $app = require_once __DIR__ . '/../../../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    } catch (\Throwable) {}
}

$c1 = LocationResolver::resolveCity('13');
$hasDb = false;
try {
    $hasDb = \Illuminate\Support\Facades\Schema::hasTable('cities');
} catch (\Throwable) {}

if ($hasDb) {
    assertTest(
        'Test 1: City ID 13 resolves to Chittorgarh via database',
        $c1 === 'Chittorgarh',
        "Resolved value: '{$c1}'"
    );
} else {
    assertTest(
        'Test 1: City ID 13 resolves without crash in offline mode',
        $c1 === '13',
        "Resolved value: '{$c1}'"
    );
}

// TEST 2: Already text name
$c2 = LocationResolver::resolveCity('Bari Sadri');
assertTest(
    'Test 2: Already text city name preserved',
    $c2 === 'Bari Sadri',
    "Resolved value: '{$c2}'"
);

// TEST 3: Null / empty city
$c3 = LocationResolver::resolveCity(null);
$c4 = LocationResolver::resolveCity('');
assertTest(
    'Test 3: Null and empty string handled gracefully',
    $c3 === '' && $c4 === '',
    "null -> '{$c3}', empty -> '{$c4}'"
);

echo "\n====================================================\n";
echo "Results: Total: " . ($passed + $failed) . ", Passed: {$passed}, Failed: {$failed}\n";
echo "====================================================\n";

exit($failed > 0 ? 1 : 0);
