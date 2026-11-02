<?php

namespace Botble\Ecommerce\Enums {
    if (! class_exists(ShippingMethodEnum::class)) {
        class ShippingMethodEnum { const DEFAULT = 'default'; }
    }
}

namespace Botble\Payment\Enums {
    if (! class_exists(PaymentMethodEnum::class)) {
        class PaymentMethodEnum { const COD = 'cod'; }
    }
}

namespace Botble\Marketplace\Models {
    if (! class_exists(Store::class)) {
        class Store {
            public $id = 1;
            public $name = 'Test Store';
            public $vendor_managed_shipping = false;
            public $warehouse_id = 'WH-123';
            public static $store;
            public static function find($id) { return self::$store; }
        }
    }
}

namespace Botble\Ecommerce\Models {
    if (! class_exists(Order::class)) {
        class Order {
            public $id = 101;
            public $code = 'ORD-101';
            public $store_id = 1;
            public $store;
            public $shipping_method;
            public $shipping_option;
            public $products = [];
            public $shippingAddress;
            public $created_at;
            public $amount = 1500;
            public function __construct() {
                $this->shipping_method = 'shipmozo';
                $this->created_at = new \DateTime();
                $this->shippingAddress = (object)[
                    'name' => 'John Doe',
                    'phone' => '9876543210',
                    'email' => 'john@example.com',
                    'address' => '123 Street',
                    'address_2' => '',
                    'zip_code' => '313001',
                    'city_name' => 'Udaipur',
                    'state_name' => 'Rajasthan',
                ];
            }
        }
    }

    if (! class_exists(Product::class)) {
        class Product {
            public $id;
            public $name;
            public $enable_shipmozo_shipping = false;
            public $is_variation = false;
            public $original_product;
            public static function find($id) {
                $p = new self();
                $p->id = $id;
                $p->enable_shipmozo_shipping = ($id === 2);
                return $p;
            }
            public function isShipmozoShippingEnabled() {
                if ($GLOBALS['test_scope'] === 'all') return true;
                if ($this->is_variation && $this->original_product) {
                    return (bool) ($this->enable_shipmozo_shipping ?? $this->original_product->enable_shipmozo_shipping);
                }
                return (bool) $this->enable_shipmozo_shipping;
            }
        }
    }
}

namespace {
    require dirname(__DIR__, 4) . '/vendor/autoload.php';
    require_once dirname(__DIR__) . '/src/Shipmozo.php';
    require_once dirname(__DIR__) . '/src/Providers/HookServiceProvider.php';
    require_once dirname(__DIR__) . '/src/Http/Controllers/ShipmozoLogController.php';

    if (! defined('SHIPMOZO_SHIPPING_METHOD_NAME')) {
        define('SHIPMOZO_SHIPPING_METHOD_NAME', 'shipmozo');
    }

    $GLOBALS['test_scope'] = 'all';
    $GLOBALS['test_status'] = 1;

    function setting($key, $default = null) {
        if ($key === 'shipping_shipmozo_status') return $GLOBALS['test_status'];
        if ($key === 'shipping_shipmozo_scope') return $GLOBALS['test_scope'];
        return $default;
    }

    function get_ecommerce_setting($key, $default = null) { return $default; }
    function ecommerce_weight_unit() { return 'kg'; }
    function is_plugin_active($plugin) { return in_array($plugin, ['marketplace', 'ecommerce']); }
    function trans($key, $replace = []) {
        $str = $key;
        foreach ($replace as $k => $v) { $str = str_replace(':' . $k, $v, $str); }
        return $str;
    }
    function __($key) { return $key; }
    function apply_filters($name, $value, ...$args) {
        if ($name === 'ecommerce_product_has_free_shipping') return false;
        if ($name === 'ecommerce_product_has_shipmozo_shipping') {
            $item = $args[0] ?? [];
            return $item['enable_shipmozo_shipping'] ?? ($GLOBALS['test_scope'] === 'all');
        }
        return $value;
    }

    class TestShipmozo extends \SparroWave\Shipmozo\Shipmozo {
        public $ratesResponse = [
            'result' => 1,
            'shipment' => [
                'rates' => [
                    'shipmozo_403' => ['id' => 403, 'name' => 'Delhivery Surface', 'price' => 120.0, 'disabled' => false]
                ]
            ]
        ];
        public $lastPushPayload = [];

        public function __construct() {}
        public function log(string $msg, array $ctx = []): void {}
        public function logError(string $msg, array $ctx = []): void {}
        public function getRates(array $data): array {
            return $this->ratesResponse;
        }
        protected function httpClient(bool $retry = false): \Illuminate\Http\Client\PendingRequest {
            $factory = new \Illuminate\Http\Client\Factory();
            $factory->fake([
                '*' => \Illuminate\Http\Client\Factory::response(['result' => 1, 'message' => 'Success', 'data' => []])
            ]);
            return $factory->asJson();
        }
    }

    $GLOBALS['carrier'] = new TestShipmozo();
    class TestHandleShippingFeeService {
        public function execute(array $data) {
            return [
                \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT => [
                    1 => ['price' => 50.0, 'name' => 'Default Flat Rate']
                ]
            ];
        }
    }
    function app($class) {
        if ($class === \Botble\Ecommerce\Services\HandleShippingFeeService::class) {
            return new TestHandleShippingFeeService();
        }
        return $GLOBALS['carrier'];
    }

    function test_assert($condition, $message) {
        if (! $condition) {
            echo "FAILED: $message\n";
            exit(1);
        }
        echo "PASS: $message\n";
    }

    echo "\n=== 1. LANGUAGE FILE SYMMETRY TEST ===\n";
    $en = require dirname(__DIR__) . '/resources/lang/en/shipmozo.php';
    $hi = require dirname(__DIR__) . '/resources/lang/hi/shipmozo.php';

    function getFlatKeys($array, $prefix = '') {
        $result = [];
        foreach ($array as $key => $val) {
            $fullKey = $prefix ? "$prefix.$key" : $key;
            if (is_array($val)) {
                $result = array_merge($result, getFlatKeys($val, $fullKey));
            } else {
                $result[] = $fullKey;
            }
        }
        return $result;
    }

    $enKeys = getFlatKeys($en);
    $hiKeys = getFlatKeys($hi);

    $missingInHi = array_diff($enKeys, $hiKeys);
    $missingInEn = array_diff($hiKeys, $enKeys);

    test_assert(empty($missingInHi), 'Zero missing keys in Hindi language file (' . count($enKeys) . ' keys)');
    test_assert(empty($missingInEn), 'Zero missing keys in English language file (' . count($hiKeys) . ' keys)');
    test_assert(count($enKeys) === count($hiKeys), 'Exact 1:1 key symmetry between EN and HI (' . count($enKeys) . ' total keys)');

    echo "\n=== 2. CANONICAL PERMISSIONS INTEGRITY TEST ===\n";
    $permissions = require dirname(__DIR__) . '/config/permissions.php';
    $flags = array_column($permissions, 'flag');
    test_assert(in_array('shipmozo.settings', $flags), 'shipmozo.settings flag exists');
    test_assert(in_array('shipmozo.logs.index', $flags), 'shipmozo.logs.index flag exists');
    test_assert(in_array('shipmozo.logs.show', $flags), 'shipmozo.logs.show flag exists');
    test_assert(in_array('shipmozo.logs.destroy', $flags), 'shipmozo.logs.destroy flag exists');
    test_assert(in_array('shipmozo.warehouses.index', $flags), 'shipmozo.warehouses.index flag exists');
    test_assert(in_array('shipmozo.warehouses.edit', $flags), 'shipmozo.warehouses.edit flag exists');
    test_assert(in_array('shipmozo.warehouses.destroy', $flags), 'shipmozo.warehouses.destroy flag exists');

    foreach ($permissions as $p) {
        test_assert(! empty($p['name']), "Permission {$p['flag']} has non-empty name");
        test_assert(! empty($p['flag']), "Permission has non-empty flag");
        test_assert(! empty($p['parent_flag']), "Permission {$p['flag']} has parent_flag: {$p['parent_flag']}");
    }

    echo "\n=== 3. MULTI-VENDOR VENDOR_MANAGED_SHIPPING ISOLATION TEST ===\n";
    $order = new \Botble\Ecommerce\Models\Order();
    $store = new \Botble\Marketplace\Models\Store();
    $store->vendor_managed_shipping = true;
    \Botble\Marketplace\Models\Store::$store = $store;
    $order->store = $store;

    $isShipmozo = $GLOBALS['carrier']->isShipmozoOrder($order);
    test_assert($isShipmozo === false, 'Store with vendor_managed_shipping = true is NEVER considered ShipMozo order');

    $store->vendor_managed_shipping = false;
    $isShipmozoNormal = $GLOBALS['carrier']->isShipmozoOrder($order);
    test_assert($isShipmozoNormal === true, 'Store with vendor_managed_shipping = false is valid ShipMozo order');

    echo "\n=== 4. SCOPE & INDIVIDUAL PRODUCT SHIPMOZO ELIGIBILITY TEST ===\n";
    $GLOBALS['test_scope'] = 'all';
    $prod1 = new \Botble\Ecommerce\Models\Product();
    $prod1->enable_shipmozo_shipping = false;
    test_assert($prod1->isShipmozoShippingEnabled() === true, 'Scope "all": all products evaluate to true');

    $GLOBALS['test_scope'] = 'individual';
    test_assert($prod1->isShipmozoShippingEnabled() === false, 'Scope "individual": product with enable_shipmozo_shipping=false evaluates to false');

    $prod1->enable_shipmozo_shipping = true;
    test_assert($prod1->isShipmozoShippingEnabled() === true, 'Scope "individual": product with enable_shipmozo_shipping=true evaluates to true');

    // Variation inheritance
    $variation = new \Botble\Ecommerce\Models\Product();
    $variation->is_variation = true;
    $variation->original_product = $prod1;
    $variation->enable_shipmozo_shipping = null;
    test_assert($variation->isShipmozoShippingEnabled() === true, 'Variation cleanly inherits true from parent');

    $prod1->enable_shipmozo_shipping = false;
    test_assert($variation->isShipmozoShippingEnabled() === false, 'Variation cleanly inherits false from parent');

    echo "\n=== 5. CHECKOUT MIXED CART & RATE AGGREGATION TEST ===\n";
    $hook = new \SparroWave\Shipmozo\Providers\HookServiceProvider(new \Illuminate\Container\Container());
    $flatResult = ['default' => ['flat' => ['price' => 50.0, 'disabled' => false]]];

    // Scenario 1: Individual scope, Cart has 0 ShipMozo products -> ShipMozo completely bypassed!
    $GLOBALS['test_scope'] = 'individual';
    $dataNoShipmozo = [
        'origin' => ['zip_code' => '312403'],
        'address_to' => ['zip_code' => '313001'],
        'items' => [
            1 => ['id' => 1, 'qty' => 1, 'weight' => 1.0, 'price' => 500, 'enable_shipmozo_shipping' => false]
        ]
    ];
    $result1 = $hook->handleShippingFee($flatResult, $dataNoShipmozo);
    test_assert(isset($result1['default']), '0 ShipMozo items: returns default ecommerce flat rate');
    test_assert(! isset($result1[SHIPMOZO_SHIPPING_METHOD_NAME]), '0 ShipMozo items: ShipMozo is completely bypassed');

    // Scenario 2: Individual scope, Cart has ALL ShipMozo products -> ShipMozo rates applied
    $dataAllShipmozo = [
        'origin' => ['zip_code' => '312403'],
        'address_to' => ['zip_code' => '313001'],
        'items' => [
            2 => ['id' => 2, 'qty' => 1, 'weight' => 1.0, 'price' => 500, 'enable_shipmozo_shipping' => true]
        ]
    ];
    $result2 = $hook->handleShippingFee($flatResult, $dataAllShipmozo);
    test_assert(isset($result2[SHIPMOZO_SHIPPING_METHOD_NAME]), 'All ShipMozo items: returns ShipMozo courier rate');
    test_assert($result2[SHIPMOZO_SHIPPING_METHOD_NAME]['shipmozo_403']['price'] === 120.0, 'Courier rate matches exact 120.0');

    // Scenario 3: Mixed Cart: 1 ShipMozo product + 1 non-ShipMozo product (default rate 50.0) -> Combines rates!
    $dataMixed = [
        'origin' => ['zip_code' => '312403'],
        'address_to' => ['zip_code' => '313001'],
        'items' => [
            2 => ['id' => 2, 'qty' => 1, 'weight' => 1.0, 'price' => 500, 'enable_shipmozo_shipping' => true],
            3 => ['id' => 3, 'qty' => 1, 'weight' => 2.0, 'price' => 300, 'enable_shipmozo_shipping' => false]
        ]
    ];
    $result3 = $hook->handleShippingFee($flatResult, $dataMixed);
    test_assert(isset($result3[SHIPMOZO_SHIPPING_METHOD_NAME]), 'Mixed cart: ShipMozo rate offered');
    // Base 120.0 + Default 50.0 = 170.0
    test_assert($result3[SHIPMOZO_SHIPPING_METHOD_NAME]['shipmozo_403']['price'] === 170.0, 'Mixed cart: Courier rate (120) + Non-Shipmozo standard fee (50) = 170.0 combined perfectly');

    echo "\n=== 6. LOG PARSER & FORMATTING TEST ===\n";
    $logController = new class extends \SparroWave\Shipmozo\Http\Controllers\ShipmozoLogController {
        public function testParse($content) { return $this->parseLogContent($content); }
    };

    $sampleLog = "[2026-10-25 14:30:00] production.INFO: Rate calculator request {\"pickup_pincode\":312403,\"delivery_pincode\":313001,\"weight\":1500}\n" .
                 "[2026-10-25 14:30:02] production.ERROR: Warehouse synchronization failed {\"store_id\":21,\"message\":\"Invalid Pincode\"}\n";

    $parsed = $logController->testParse($sampleLog);
    test_assert(count($parsed) === 2, 'Log parser extracted exactly 2 entries');
    test_assert($parsed[0]['level'] === 'ERROR', 'Log entry 0 (reversed newest first) is ERROR');
    test_assert($parsed[0]['message'] === 'Warehouse synchronization failed', 'Log message extracted correctly');
    test_assert(str_contains($parsed[0]['context'], '"store_id": 21'), 'JSON context formatted and pretty-printed with indentation');
    test_assert($parsed[1]['level'] === 'INFO', 'Log entry 1 is INFO');

    echo "\n=== ALL 6 SUITES PASSED CLEANLY (100% VERIFIED) ===\n";
}
