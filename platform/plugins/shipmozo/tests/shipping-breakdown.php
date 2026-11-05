<?php

// Run with: php platform/plugins/shipmozo/tests/shipping-breakdown.php
$root = dirname(__DIR__, 4);
chdir($root);
$loader = require $root . '/vendor/autoload.php';
$loader->addPsr4('Botble\\Ecommerce\\', $root . '/platform/plugins/ecommerce/src/');
$loader->addPsr4('SparroWave\\Shipmozo\\', $root . '/platform/plugins/shipmozo/src/');
require $root . '/platform/plugins/shipmozo/helpers/constants.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->instance('request', \Illuminate\Http\Request::create('/checkout', 'POST'));

use SparroWave\Shipmozo\Services\CheckoutShippingFeeService;
use SparroWave\Shipmozo\Supports\ShippingBreakdown;

class ShippingBreakdownFakeFeeService extends CheckoutShippingFeeService
{
    public function __construct() {}

    protected function getCacheKey(array $data): string { return 'snapshot-test'; }

    protected function getCacheValue(string $key): array|\Illuminate\Contracts\Cache\Repository|string|null { return null; }

    protected function setCacheValue(string $key, mixed $value): bool { return true; }

    protected function getShippingFee(array $data, string $method, ?string $option = null): array { return []; }
}

$rate = [
    'id' => 'rate-1', 'price' => 349, 'customer_shipmozo_price' => 249,
    'direct_shipping_fee' => 100, 'is_mixed' => true,
    'shipmozo_weight' => 2.5, 'direct_weight' => 1, 'weight_unit' => 'kg',
];
$breakdown = ShippingBreakdown::fromRate($rate);
if (! $breakdown || $breakdown['total'] !== 349.0 || $breakdown['direct'] !== 100.0
    || $breakdown['shipmozo_weight'] !== 2.5) {
    throw new RuntimeException('Mixed quote was not captured accurately.');
}

foreach ([
    ['price' => 348], ['direct_shipping_fee' => 'invalid'], ['is_mixed' => false],
] as $invalid) {
    if (ShippingBreakdown::fromRate(array_replace($rate, $invalid)) !== null) {
        throw new RuntimeException('Invalid mixed quote was accepted.');
    }
}

add_filter('handle_shipping_fee', fn () => [SHIPMOZO_SHIPPING_METHOD_NAME => ['rate-1' => $rate]], 130, 2);
$selected = (new ShippingBreakdownFakeFeeService())->execute(
    ['extra' => ['store_id' => 12]], SHIPMOZO_SHIPPING_METHOD_NAME, 'rate-1'
);
$quotes = request()->attributes->get('shipmozo_selected_shipping_quotes');
if (($selected[0]['price'] ?? null) !== 349 || ($quotes['12']['direct'] ?? null) !== 100.0) {
    throw new RuntimeException('Selected quote was not scoped to its store.');
}

echo "ShipMozo shipping breakdown checks passed.\n";
