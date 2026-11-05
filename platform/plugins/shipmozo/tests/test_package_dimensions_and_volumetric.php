<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../src/Supports/PackageDimensionsCalculator.php';

use SparroWave\Shipmozo\Supports\PackageDimensionsCalculator;

echo "====================================================\n";
echo "Testing PackageDimensionsCalculator (ShipMozo Multi-Item Dimensions & Volumetric Weight)\n";
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

// ----------------------------------------------------
// TEST 1: Single item where dead weight > volumetric weight
// ----------------------------------------------------
$singleHeavy = [
    [
        'id' => 101,
        'name' => 'Heavy Metal Tool',
        'qty' => 1,
        'weight' => 2.5, // 2.5 kg = 2500g
        'length' => 15,
        'wide' => 12,
        'height' => 10,
    ]
];
$res1 = PackageDimensionsCalculator::calculate($singleHeavy);
assertTest(
    'Test 1: Single heavy item (Dead weight dominates)',
    $res1['dead_weight_grams'] === 2500 &&
    $res1['length'] === 15 &&
    $res1['width'] === 12 &&
    $res1['height'] === 10 &&
    $res1['chargeable_weight_grams'] === 2500,
    sprintf("Dead: %dg, Vol: %dg, Chargeable: %dg, Dims: %dx%dx%d cm",
        $res1['dead_weight_grams'], $res1['volumetric_weight_grams'], $res1['chargeable_weight_grams'],
        $res1['length'], $res1['width'], $res1['height']
    )
);

// ----------------------------------------------------
// TEST 2: Order #20143 (User's exact real order from Gropart)
// Item 1 (4104): 12x8x8 cm, 0.15 kg (150g)
// Item 2 (4105): 32x13x13 cm, 1.08 kg (1080g)
// ----------------------------------------------------
$order20143Items = [
    [
        'id' => 4104,
        'name' => 'Product 4104',
        'qty' => 1,
        'weight' => 0.15,
        'length' => 12,
        'wide' => 8,
        'height' => 8,
    ],
    [
        'id' => 4105,
        'name' => 'Product 4105',
        'qty' => 1,
        'weight' => 1.08,
        'length' => 32,
        'wide' => 13,
        'height' => 13,
    ],
];
$res2 = PackageDimensionsCalculator::calculate($order20143Items);
// Expected:
// Dead weight: 150g + 1080g = 1230g (1.23 kg)
// Dimensions: 32 x 21 x 13 cm
// Volumetric weight: (32 * 21 * 13 / 5000) * 1000 = 1747g (1.75 kg)
// Chargeable weight: max(1230, 1747) = 1747g (1.75 kg)
assertTest(
    'Test 2: Order #20143 multi-item volumetric calculation',
    $res2['dead_weight_grams'] === 1230 &&
    $res2['volumetric_weight_grams'] === 1747 &&
    $res2['chargeable_weight_grams'] === 1747 &&
    $res2['length'] === 32 &&
    $res2['width'] === 21 &&
    $res2['height'] === 13,
    sprintf("Dead: %dg (1.23kg), Vol: %dg (1.75kg), Billable: %dg (1.75kg), Box Dims: %dx%dx%d cm",
        $res2['dead_weight_grams'], $res2['volumetric_weight_grams'], $res2['chargeable_weight_grams'],
        $res2['length'], $res2['width'], $res2['height']
    )
);

// ----------------------------------------------------
// TEST 3: Multi-quantity items (qty = 2 of 32x13x13 item)
// ----------------------------------------------------
$multiQtyItems = [
    [
        'id' => 4105,
        'name' => 'Product 4105',
        'qty' => 2,
        'weight' => 1.08,
        'length' => 32,
        'wide' => 13,
        'height' => 13,
    ]
];
$res3 = PackageDimensionsCalculator::calculate($multiQtyItems);
assertTest(
    'Test 3: Multiple quantity of same item (qty = 2)',
    $res3['dead_weight_grams'] === 2160 &&
    $res3['chargeable_weight_grams'] > 2160 &&
    $res3['length'] === 32,
    sprintf("Dead: %dg, Vol: %dg, Billable: %dg, Dims: %dx%dx%d cm",
        $res3['dead_weight_grams'], $res3['volumetric_weight_grams'], $res3['chargeable_weight_grams'],
        $res3['length'], $res3['width'], $res3['height']
    )
);

// ----------------------------------------------------
// TEST 4: Empty items array & zero weights fallback
// ----------------------------------------------------
$res4 = PackageDimensionsCalculator::calculate([]);
assertTest(
    'Test 4: Empty items array safe courier fallbacks',
    $res4['dead_weight_grams'] === PackageDimensionsCalculator::MIN_WEIGHT_GRAMS &&
    $res4['length'] === PackageDimensionsCalculator::MIN_DIMENSION &&
    $res4['width'] === PackageDimensionsCalculator::MIN_DIMENSION &&
    $res4['height'] === PackageDimensionsCalculator::MIN_DIMENSION,
    sprintf("Safe fallbacks: %dg, Dims: %dx%dx%d cm",
        $res4['chargeable_weight_grams'], $res4['length'], $res4['width'], $res4['height']
    )
);

// ----------------------------------------------------
// TEST 5: Weight normalization (gram vs kg inputs)
// ----------------------------------------------------
$w1 = PackageDimensionsCalculator::normalizeWeightInGrams(0.15); // should be 150g
$w2 = PackageDimensionsCalculator::normalizeWeightInGrams(1.08); // should be 1080g
$w3 = PackageDimensionsCalculator::normalizeWeightInGrams(500);  // 500g
$w4 = PackageDimensionsCalculator::normalizeWeightInGrams(0);    // fallback 200g
assertTest(
    'Test 5: Weight normalization for decimals and zero values',
    $w1 === 150 && $w2 === 1080 && $w3 === 500 && $w4 === 200,
    sprintf("0.15kg -> %dg, 1.08kg -> %dg, 500g -> %dg, 0g -> %dg", $w1, $w2, $w3, $w4)
);

// ----------------------------------------------------
// TEST 6: Simulated getRates & pushOrder payload for Order #20143
// ----------------------------------------------------
$packageCalc = PackageDimensionsCalculator::calculate($order20143Items);
$ratePayload = [
    'pickup_pincode' => 312403,
    'delivery_pincode' => 313001,
    'weight' => (int) $packageCalc['chargeable_weight_grams'],
    'dimensions' => [
        [
            'no_of_box' => '1',
            'length' => (string) max($packageCalc['length'], 1),
            'width' => (string) max($packageCalc['width'], 1),
            'height' => (string) max($packageCalc['height'], 1),
        ],
    ],
    'payment_type' => 'PREPAID',
    'shipment_type' => 'FORWARD',
];

assertTest(
    'Test 6: Simulated ShipMozo payload sends 1747g (1.75kg) and 32x21x13 cm box',
    $ratePayload['weight'] === 1747 &&
    $ratePayload['dimensions'][0]['length'] === '32' &&
    $ratePayload['dimensions'][0]['width'] === '21' &&
    $ratePayload['dimensions'][0]['height'] === '13',
    sprintf("Payload weight: %d, Dims: %sx%sx%s cm",
        $ratePayload['weight'],
        $ratePayload['dimensions'][0]['length'],
        $ratePayload['dimensions'][0]['width'],
        $ratePayload['dimensions'][0]['height']
    )
);

echo "\n====================================================\n";
echo "Results: Total: " . ($passed + $failed) . ", Passed: {$passed}, Failed: {$failed}\n";
echo "====================================================\n";

exit($failed > 0 ? 1 : 0);
