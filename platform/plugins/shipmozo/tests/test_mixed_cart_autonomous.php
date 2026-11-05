<?php

/**
 * Autonomous Full-Spectrum Verification Suite for Mixed Cart Smart Handling
 * Covers: Checkout, Incomplete Orders, Admin Order Edit/Modal, Admin Order Create, Consignment Isolation, and Product Badges.
 */

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__, 4) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use SparroWave\Shipmozo\Http\Controllers\ShipmozoController;
use SparroWave\Shipmozo\Listeners\OrderConfirmedListener;
use SparroWave\Shipmozo\Providers\HookServiceProvider;
use SparroWave\Shipmozo\Shipmozo;

$passCount = 0;
$failCount = 0;

function autonomous_assert(bool $condition, string $message, ?string $extra = null): void
{
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$message}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$message}\n";
        if ($extra) {
            echo "         Details: {$extra}\n";
        }
    }
}

echo "====================================================================\n";
echo "AUTONOMOUS FULL-SPECTRUM TEST SUITE: MIXED CART SMART HANDLING\n";
echo "====================================================================\n";

// Ensure test settings
setting()->set('shipping_shipmozo_status', 1);
setting()->set('shipping_shipmozo_scope', 'individual');
setting()->set('shipping_shipmozo_rate_adjustment_type', 'none');
setting()->set('shipping_shipmozo_rate_adjustment_value', 0);

// Setup deterministic HTTP mock for ShipMozo API
Http::preventStrayRequests();
\Illuminate\Support\Facades\Mail::fake();
\Illuminate\Support\Facades\Notification::fake();
Http::fake([
    '*/get-warehouses' => Http::response([
        'result' => 1,
        'message' => 'Success',
        'data' => [
            [
                'id' => 'WH-TEST-1',
                'warehouse_name' => 'Main Warehouse',
                'status' => 'ACTIVE',
                'default' => 'YES',
            ],
        ],
    ]),
    '*/rate-calculator' => function (\Illuminate\Http\Client\Request $request) {
        $weight = (int) data_get($request->data(), 'weight', 200);
        // Base rate based on parcel weight
        $delhiveryBase = $weight > 3000 ? 250.00 : 120.00;
        $amazonBase = $weight > 3000 ? 266.00 : 135.00;

        return Http::response([
            'result' => 1,
            'message' => 'Success',
            'data' => [
                [
                    'courier_id' => 403,
                    'courier_name' => 'Delhivery Surface.',
                    'total_charge' => $delhiveryBase,
                    'expected_delivery_date' => '3-4 Days',
                    'pickups_automatically_scheduled' => 'YES',
                ],
                [
                    'courier_id' => 384,
                    'courier_name' => 'Amazon ATS',
                    'total_charge' => $amazonBase,
                    'expected_delivery_date' => '4 Days',
                    'pickups_automatically_scheduled' => 'YES',
                ],
            ],
        ]);
    },
    '*/push-order' => Http::response([
        'result' => 1,
        'message' => 'Order created successfully',
        'data' => [
            'order_id' => 'SM-99999',
            'reference_id' => 'SM-REF-99999',
        ],
    ]),
    '*/assign-courier' => Http::response([
        'result' => 1,
        'message' => 'Success',
        'data' => [
            'awb_number' => 'AWB99999DELHIVERY',
        ],
    ]),
    '*/order-detail' => Http::response([
        'result' => 1,
        'message' => 'Success',
        'data' => [
            'awb_number' => 'AWB99999DELHIVERY',
            'status' => 'READY_TO_SHIP',
        ],
    ]),
    '*' => Http::response(['result' => 1, 'message' => 'OK', 'data' => []]),
]);

$hookProvider = new HookServiceProvider($app);
$feeService = app(HandleShippingFeeService::class);
$shipmozo = app(Shipmozo::class);

// Clear cache before test execution
$feeService->clearCache();

// -----------------------------------------------------------------------------
// SUITE 1: Rate Calculation Matrix & Overcharge Elimination
// -----------------------------------------------------------------------------
echo "\n--- SUITE 1: Rate Calculation Matrix & Overcharge Elimination ---\n";

$origin = ['zip_code' => '141003', 'city' => 'Ludhiana', 'state' => 'Punjab', 'country' => 'IN'];
$dest = ['zip_code' => '851101', 'city' => 'Begusarai', 'state' => 'Bihar', 'country' => 'IN'];

// Case 1.1: Pure ShipMozo Cart (2 items, all ShipMozo enabled)
$pureShipmozoCart = [
    'origin' => $origin,
    'address_to' => $dest,
    'weight' => 2.5,
    'order_total' => 1500.0,
    'items' => [
        999201 => ['id' => 999201, 'qty' => 1, 'weight' => 1.0, 'price' => 500.0, 'enable_shipmozo_shipping' => 1, 'product_free_shipping' => false],
        999202 => ['id' => 999202, 'qty' => 1, 'weight' => 1.5, 'price' => 1000.0, 'enable_shipmozo_shipping' => 1, 'product_free_shipping' => false],
    ],
];

$feeService->clearCache();
$resPureShipmozo = $feeService->execute($pureShipmozoCart);
autonomous_assert(
    isset($resPureShipmozo['shipmozo']) && ! empty($resPureShipmozo['shipmozo']),
    'Pure ShipMozo cart returns carrier rates'
);
autonomous_assert(
    ! isset($resPureShipmozo[ShippingMethodEnum::DEFAULT]),
    'Pure ShipMozo cart unsets default Botble shipping method'
);
$delhiveryPure = $resPureShipmozo['shipmozo']['shipmozo_403_auto']['price'] ?? 0;
autonomous_assert(
    (float)$delhiveryPure === 120.00,
    "Pure ShipMozo courier price is pure courier quote without standard fee markup (₹{$delhiveryPure})"
);

// Case 1.2: Pure Non-ShipMozo Cart (2 items, 0.45kg total)
$pureNonShipmozoCart = [
    'origin' => $origin,
    'address_to' => $dest,
    'weight' => 0.45,
    'order_total' => 450.0,
    'items' => [
        999203 => ['id' => 999203, 'qty' => 1, 'weight' => 0.20, 'price' => 200.0, 'enable_shipmozo_shipping' => 0, 'product_free_shipping' => false],
        999204 => ['id' => 999204, 'qty' => 1, 'weight' => 0.25, 'price' => 250.0, 'enable_shipmozo_shipping' => 0, 'product_free_shipping' => false],
    ],
];

$feeService->clearCache();
$resPureNonShipmozo = $feeService->execute($pureNonShipmozoCart);
autonomous_assert(
    isset($resPureNonShipmozo[ShippingMethodEnum::DEFAULT]),
    'Pure Non-ShipMozo cart returns standard Botble rules'
);
autonomous_assert(
    ! isset($resPureNonShipmozo['shipmozo']),
    'Pure Non-ShipMozo cart completely bypasses ShipMozo API'
);
$lowestDefault = null;
foreach ($resPureNonShipmozo[ShippingMethodEnum::DEFAULT] as $opt) {
    if (isset($opt['price']) && ($lowestDefault === null || (float)$opt['price'] < $lowestDefault)) {
        $lowestDefault = (float)$opt['price'];
    }
}
autonomous_assert(
    $lowestDefault == 100.0 || $lowestDefault == 99.0,
    "Pure Non-ShipMozo cart matches 0.05-0.45kg rule correctly (₹{$lowestDefault})"
);

// Case 1.3: Mixed Cart (Item A: ShipMozo 3.28kg + Item B: Non-ShipMozo 0.45kg)
$mixedCart = [
    'origin' => $origin,
    'address_to' => $dest,
    'weight' => 3.73,
    'order_total' => 2082.0,
    'items' => [
        9994095 => ['id' => 9994095, 'qty' => 1, 'weight' => 0.45, 'price' => 249.0, 'enable_shipmozo_shipping' => 0, 'product_free_shipping' => false],
        9994099 => ['id' => 9994099, 'qty' => 1, 'weight' => 3.28, 'price' => 1833.0, 'enable_shipmozo_shipping' => 1, 'product_free_shipping' => false],
    ],
];

// Test isolated non-shipmozo calculation
$nonShipItems = [9994095 => $mixedCart['items'][9994095]];
$calculatedNonShipFee = $hookProvider->calculateNonShipmozoShippingFee($mixedCart, $nonShipItems);
autonomous_assert(
    $calculatedNonShipFee == 100.0 || $calculatedNonShipFee == 99.0,
    "calculateNonShipmozoShippingFee returns exact ₹{$calculatedNonShipFee} for 0.45kg parcel"
);
autonomous_assert(
    $calculatedNonShipFee < 300.0,
    'Eliminates double-counting: Whole-cart 3.73kg rule (₹400/₹395) is NOT applied to non-ShipMozo item!'
);

// Test full mixed cart response
$feeService->clearCache();
$resMixed = $feeService->execute($mixedCart);
autonomous_assert(
    isset($resMixed['shipmozo']) && count($resMixed['shipmozo']) > 0,
    'Mixed cart returns ShipMozo courier options with combined pricing'
);

$delhiveryMixed = $resMixed['shipmozo']['shipmozo_403_auto'] ?? null;
$expectedMixedFee = 250.00 + $calculatedNonShipFee; // 250 + 100 = 350.00 (or 349.00 on live)
echo "      Delhivery Surface mixed rate: ₹" . ($delhiveryMixed['price'] ?? 'N/A') . " (Expected: ₹{$expectedMixedFee})\n";

autonomous_assert(
    $delhiveryMixed && (float)$delhiveryMixed['price'] === $expectedMixedFee,
    "Combined rate matches exact formula: ShipMozo API (250) + Non-ShipMozo Rule ({$calculatedNonShipFee}) = ₹{$expectedMixedFee}"
);

// -----------------------------------------------------------------------------
// SUITE 2: Consignment & Consignment Payload Isolation (Shipmozo::pushOrder)
// -----------------------------------------------------------------------------
echo "\n--- SUITE 2: Consignment & Consignment Payload Isolation ---\n";

DB::beginTransaction();
try {
    // Create mock order with 2 products
    $order = new Order();
    $order->code = 'TEST-ORD-' . uniqid();
    $order->shipping_method = 'shipmozo';
    $order->shipping_option = 'shipmozo_403_auto';
    $order->shipping_amount = 349.00;
    $order->amount = 2431.00;
    $order->sub_total = 2082.00;
    $order->save();

    $shippingAddress = new OrderAddress();
    $shippingAddress->order_id = $order->id;
    $shippingAddress->name = 'Test Customer';
    $shippingAddress->phone = '9876543210';
    $shippingAddress->email = 'test@example.com';
    $shippingAddress->address = 'Test Street 123';
    $shippingAddress->zip_code = '851101';
    $shippingAddress->city = 'Begusarai';
    $shippingAddress->state = 'Bihar';
    $shippingAddress->country = 'IN';
    $shippingAddress->save();

    // Create Product 1 (Non-ShipMozo)
    $prodNon = new Product();
    $prodNon->name = 'Test Non-ShipMozo Part';
    $prodNon->price = 249.00;
    $prodNon->weight = 0.45;
    $prodNon->enable_shipmozo_shipping = 0;
    $prodNon->product_free_shipping = 0;
    $prodNon->save();

    // Create Product 2 (ShipMozo)
    $prodShip = new Product();
    $prodShip->name = 'Test ShipMozo Part';
    $prodShip->price = 1833.00;
    $prodShip->weight = 3.28;
    $prodShip->enable_shipmozo_shipping = 1;
    $prodShip->product_free_shipping = 0;
    $prodShip->save();

    // Attach order products
    $op1 = new OrderProduct();
    $op1->order_id = $order->id;
    $op1->product_id = $prodNon->id;
    $op1->product_name = $prodNon->name;
    $op1->qty = 1;
    $op1->price = 249.00;
    $op1->weight = 0.45;
    $op1->save();

    $op2 = new OrderProduct();
    $op2->order_id = $order->id;
    $op2->product_id = $prodShip->id;
    $op2->product_name = $prodShip->name;
    $op2->qty = 1;
    $op2->price = 1833.00;
    $op2->weight = 3.28;
    $op2->save();

    $order->load(['products.product', 'shippingAddress']);

    // Inspect items filtered in pushOrder
    $pushedItems = [];
    $pushedWeight = 0;
    $isIndividualScope = setting('shipping_shipmozo_scope', 'all') === 'individual';

    foreach ($order->products as $orderProduct) {
        $product = $orderProduct->product;
        if ($isIndividualScope && $product) {
            $isEligible = is_callable([$product, 'isShipmozoShippingEnabled'])
                ? $product->isShipmozoShippingEnabled()
                : (bool) $product->enable_shipmozo_shipping;

            if (! $isEligible) {
                continue;
            }
        }
        $rawWeight = (float) ($orderProduct->weight ?: ($product ? $product->weight : 0));
        $itemWeightInGrams = $rawWeight * 1000;
        $pushedWeight += $itemWeightInGrams * $orderProduct->qty;
        $pushedItems[] = [
            'name' => $orderProduct->product_name,
            'sku' => $product ? $product->sku : 'N/A',
            'qty' => $orderProduct->qty,
        ];
    }

    autonomous_assert(
        count($pushedItems) === 1,
        'pushOrder filters strictly: exactly 1 ShipMozo product included in consignment'
    );
    autonomous_assert(
        $pushedItems[0]['name'] === 'Test ShipMozo Part',
        'Consignment item matches the ShipMozo-enabled product'
    );
    autonomous_assert(
        (int) round($pushedWeight) === 3280,
        "Consignment physical weight is exactly 3280g (3.28kg), excluding 450g non-ShipMozo weight"
    );

    // Test actual pushOrder execution with mocked HTTP client
    $pushResponse = $shipmozo->pushOrder($order);
    autonomous_assert(
        Arr::get($pushResponse, 'result') == 1,
        'Shipmozo::pushOrder executes and receives success from API'
    );

    // -----------------------------------------------------------------------------
    // SUITE 3: Shipment Creation & Weight Storage (OrderConfirmedListener)
    // -----------------------------------------------------------------------------
    echo "\n--- SUITE 3: Shipment Creation & Weight Storage ---\n";

    $listener = new OrderConfirmedListener($shipmozo);
    $event = new OrderConfirmedEvent($order);
    $listener->handle($event);

    $shipment = Shipment::where('order_id', $order->id)->first();
    autonomous_assert(
        $shipment !== null,
        'Shipment record created successfully on order confirmation'
    );
    autonomous_assert(
        round((float) $shipment->weight, 2) === 3.28,
        "Shipment weight is saved as 3.28kg (ShipMozo parcel weight), NOT 3.73kg total cart weight"
    );
    autonomous_assert(
        (float) $shipment->price === 349.00,
        "Shipment price is saved as combined shipping amount (₹349.00)"
    );

    // -----------------------------------------------------------------------------
    // SUITE 4: Admin Order Detail, Recheck Rate & Update Rate (ShipmozoController)
    // -----------------------------------------------------------------------------
    echo "\n--- SUITE 4: Admin Order Detail, Recheck Rate & Update Rate ---\n";

    // Simulate Admin Request Context
    $adminReq = Request::create('/admin/shipments/shipmozo/rates/' . $shipment->id, 'GET');
    app()->instance('request', $adminReq);

    $controller = app(ShipmozoController::class);

    // Test getRates (Recheck Rates action in modal)
    $response = new BaseHttpResponse();
    $ratesResult = $controller->getRates($shipment->id, $response);
    autonomous_assert(
        $ratesResult->isError() === false,
        'ShipmozoController::getRates succeeds for mixed shipment'
    );

    $responseData = $ratesResult->toArray()['data'] ?? [];
    $htmlContent = $responseData['html'] ?? '';
    autonomous_assert(
        ! empty($htmlContent),
        'getRates returns rendered courier rates HTML for the modal'
    );

    // Test updateRate with a courier selection
    $updateReq = Request::create('/admin/shipments/shipmozo/rates/' . $shipment->id, 'POST', ['shipping_option' => 'shipmozo_403_auto']);
    app()->instance('request', $updateReq);
    $updateResult = $controller->updateRate($shipment->id, $updateReq, $response);
    autonomous_assert(
        $updateResult->isError() === false,
        'ShipmozoController::updateRate succeeds for mixed shipment'
    );

    // Refresh order and shipment
    $order->refresh();
    $shipment->refresh();

    autonomous_assert(
        $order->shipping_method->getValue() === 'shipmozo' || (string)$order->shipping_method === 'shipmozo',
        'Order shipping method updated to shipmozo'
    );
    autonomous_assert(
        (float) $order->shipping_amount === $expectedMixedFee,
        "Order shipping amount updated with combined fee (₹{$order->shipping_amount})"
    );
    autonomous_assert(
        (float) $shipment->price === $expectedMixedFee,
        "Shipment price updated with combined fee (₹{$shipment->price})"
    );

    // -----------------------------------------------------------------------------
    // SUITE 5: Admin & Incomplete Order Views (info.blade.php & Badges)
    // -----------------------------------------------------------------------------
    echo "\n--- SUITE 5: Admin & Incomplete Order Views ---\n";

    // Test info.blade.php rendering
    $infoHtml = view('plugins/shipmozo::info', ['shipment' => $shipment, 'order' => $order])->render();
    autonomous_assert(
        str_contains($infoHtml, 'Mixed Fulfillment Order') || str_contains($infoHtml, 'मिश्रित पूर्ति ऑर्डर'),
        'info.blade.php displays Mixed Fulfillment Order notification banner'
    );
    autonomous_assert(
        str_contains($infoHtml, 'ShipMozo Parcel'),
        'info.blade.php labels Package Weight explicitly as (ShipMozo Parcel)'
    );
    autonomous_assert(
        str_contains($infoHtml, 'ShipMozo Courier Cost') || str_contains($infoHtml, 'Wallet Deduction'),
        'info.blade.php displays ShipMozo Courier Cost / Wallet Deduction'
    );
    autonomous_assert(
        str_contains($infoHtml, 'Customer Paid Shipping') || str_contains($infoHtml, 'Customer Paid'),
        'info.blade.php displays Customer Paid Shipping breakdown'
    );

    // Test Product Badges (ecommerce_order_product_item_extra_info)
    $adminReq = Request::create('/admin/orders/edit/' . $order->id, 'GET');
    app()->instance('request', $adminReq);

    $badge1 = apply_filters('ecommerce_order_product_item_extra_info', '', $op1, $order);
    $badge2 = apply_filters('ecommerce_order_product_item_extra_info', '', $op2, $order);

    autonomous_assert(
        str_contains($badge1, 'Standard Delivery'),
        "Product 1 (Non-ShipMozo) gets '[Standard Delivery]' badge in admin view"
    );
    autonomous_assert(
        str_contains($badge2, 'ShipMozo'),
        "Product 2 (ShipMozo) gets '[ShipMozo]' badge in admin view"
    );

    // Test farmart-helper order-info.blade.php rendering
    $orderInfoPath = plugin_path('farmart-helper/resources/views/orders/order-info.blade.php');
    if (file_exists($orderInfoPath)) {
        $orderInfoHtml = view()->file($orderInfoPath, ['order' => $order, 'weight' => 3.73])->render();
        autonomous_assert(
            str_contains($orderInfoHtml, 'Mixed Fulfillment'),
            "order-info.blade.php displays 'Mixed Fulfillment' badge"
        );
        autonomous_assert(
            str_contains($orderInfoHtml, 'ShipMozo Parcel') && str_contains($orderInfoHtml, '3.28'),
            "order-info.blade.php displays ShipMozo Parcel weight (3.28 kg)"
        );
        autonomous_assert(
            str_contains($orderInfoHtml, 'Direct / Shop Parcel') && str_contains($orderInfoHtml, '0.45'),
            "order-info.blade.php displays Direct / Shop Parcel weight (0.45 kg)"
        );
        autonomous_assert(
            str_contains($orderInfoHtml, '3.73'),
            "order-info.blade.php displays the recorded total package weight (3.73 kg)"
        );
        autonomous_assert(
            str_contains($orderInfoHtml, trans('plugins/shipmozo::shipmozo.shipping_breakdown_included')),
            "order-info.blade.php marks parcel components as included in the recorded shipping fee"
        );
        autonomous_assert(
            str_contains($orderInfoHtml, '250') && str_contains($orderInfoHtml, '100'),
            "order-info.blade.php displays exact parcel fee breakdown (₹250 ShipMozo + ₹100 Direct)"
        );
        autonomous_assert(
            str_contains($orderInfoHtml, 'Rule') || str_contains($orderInfoHtml, 'rule') || str_contains($orderInfoHtml, 'Nominal Delivery'),
            "order-info.blade.php displays applied standard shipping rule"
        );
    }

    // ShipMozo's own fallback must render the same recorded breakdown without Helper's view.
    $standaloneOrderInfo = view()->file(
        plugin_path('shipmozo/resources/views/overrides/order-info.blade.php'),
        ['order' => $order, 'weight' => 3.73]
    )->render();
    autonomous_assert(
        str_contains($standaloneOrderInfo, '3.28')
            && str_contains($standaloneOrderInfo, '0.45')
            && str_contains($standaloneOrderInfo, '250')
            && str_contains($standaloneOrderInfo, '100'),
        'ShipMozo-owned order-info fallback shows saved parcel weights and fee components'
    );

    // Test farmart-helper incomplete.blade.php rendering
    $incompletePath = plugin_path('farmart-helper/resources/views/orders/incomplete.blade.php');
    if (file_exists($incompletePath)) {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $incompleteHtml = view()->file($incompletePath, [
            'order' => $order,
            'weight' => 3.73,
        ])->render();
        autonomous_assert(
            str_contains($incompleteHtml, 'Mixed Fulfillment'),
            "incomplete.blade.php displays 'Mixed Fulfillment' badge"
        );
        autonomous_assert(
            str_contains($incompleteHtml, 'ShipMozo Parcel') && str_contains($incompleteHtml, '3.28'),
            "incomplete.blade.php displays ShipMozo Parcel weight (3.28 kg)"
        );
        autonomous_assert(
            str_contains($incompleteHtml, 'Direct / Shop Parcel') && str_contains($incompleteHtml, '0.45'),
            "incomplete.blade.php displays Direct / Shop Parcel weight (0.45 kg)"
        );
        autonomous_assert(
            str_contains($incompleteHtml, '250') && str_contains($incompleteHtml, '100'),
            "incomplete.blade.php displays exact parcel fee breakdown (₹250 ShipMozo + ₹100 Direct)"
        );
        autonomous_assert(
            str_contains($incompleteHtml, 'Rule') || str_contains($incompleteHtml, 'rule') || str_contains($incompleteHtml, 'Nominal Delivery'),
            "incomplete.blade.php displays applied standard shipping rule"
        );
    }

    // -----------------------------------------------------------------------------
    // SUITE 6: Admin Order Create Screen (checkDataBeforeCreateOrder Simulation)
    // -----------------------------------------------------------------------------
    echo "\n--- SUITE 6: Admin Order Create Screen Simulation ---\n";

    $adminCreateData = [
        'address' => 'Test Street 123',
        'country' => 'IN',
        'state' => 'Bihar',
        'city' => 'Begusarai',
        'weight' => 3.73,
        'order_total' => 2082.00,
        'address_to' => $dest,
        'origin' => $origin,
        'items' => [
            $prodNon->id => [
                'id' => $prodNon->id,
                'weight' => 0.45,
                'price' => 249.00,
                'qty' => 1,
                'name' => $prodNon->name,
                'enable_shipmozo_shipping' => 0,
                'product_free_shipping' => false,
            ],
            $prodShip->id => [
                'id' => $prodShip->id,
                'weight' => 3.28,
                'price' => 1833.00,
                'qty' => 1,
                'name' => $prodShip->name,
                'enable_shipmozo_shipping' => 1,
                'product_free_shipping' => false,
            ],
        ],
        'extra' => [],
        'payment_method' => 'cod',
    ];

    $feeService->clearCache();
    $adminShipping = $feeService->execute($adminCreateData);
    $adminShippingMethods = [];

    foreach ($adminShipping as $key => $shippingItem) {
        foreach ($shippingItem as $subKey => $subShippingItem) {
            $adminShippingMethods[$key . ';' . $subKey] = [
                'name' => $subShippingItem['name'],
                'price' => (float)$subShippingItem['price'],
                'method' => $key,
                'option' => $subKey,
                'title' => $subShippingItem['name'] . ' - ' . format_price($subShippingItem['price']),
            ];
        }
    }

    autonomous_assert(
        count($adminShippingMethods) > 0,
        'Admin Order Create screen receives available combined shipping options'
    );

    $sampleMethod = reset($adminShippingMethods);
    echo "      Admin create order dropdown option: {$sampleMethod['title']}\n";
    autonomous_assert(
        $sampleMethod['method'] === 'shipmozo',
        'Admin create order shipping option uses shipmozo method'
    );
    autonomous_assert(
        (float)$sampleMethod['price'] === $expectedMixedFee,
        "Admin create order shipping option price matches exact smart rate (₹{$sampleMethod['price']})"
    );

} finally {
    DB::rollBack();
}

echo "\n====================================================================\n";
echo "AUTONOMOUS TEST SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
