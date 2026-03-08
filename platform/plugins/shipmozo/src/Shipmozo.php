<?php

namespace SparroWave\Shipmozo;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Shipmozo
{
    protected string $baseUrl = 'https://shipping-api.com/app/api/v1';
    protected ?string $publicKey;
    protected ?string $privateKey;
    protected bool $logging;

    public function __construct()
    {
        $this->publicKey = setting('shipping_shipmozo_public_key');
        $this->privateKey = setting('shipping_shipmozo_private_key');
        $this->logging = (bool) setting('shipping_shipmozo_logging', 0);
    }

    public function getName(): string
    {
        return 'Shipmozo';
    }

    public function canCreateTransaction(Shipment $shipment): bool
    {
        if (!$shipment->order || $shipment->tracking_id) {
            return false;
        }

        $method = $shipment->order->shipping_method->getValue();

        // Botble Marketplace sets parent "Incomplete" cart shipping methods to "default" because it drops the array.
        // If it is 'default' but has a numeric shipping_option, we still permit the transaction attempt.
        if ($method === SHIPMOZO_SHIPPING_METHOD_NAME) {
            return true;
        }

        if ($method === \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT && is_numeric($shipment->order->shipping_option)) {
            return true;
        }

        return false;
    }

    public function getRoutePrefixByFactor(): string
    {
        return is_in_admin(true) ? 'ecommerce.shipments.' : 'marketplace.vendor.orders.';
    }

    protected function getHeaders(): array
    {
        return [
            'public-key' => $this->publicKey,
            'private-key' => $this->privateKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function log(string $message, array $context = [])
    {
        if ($this->logging) {
            Log::channel('shipmozo')->info($message, $context);
        }
    }

    protected function logError(string $message, array $context = [])
    {
        if ($this->logging) {
            Log::channel('shipmozo')->error($message, $context);
        }
    }

    public function checkPincodeServiceability(string $pickupPincode, string $deliveryPincode): array
    {
        $payload = [
            'pickup_pincode' => (int) $pickupPincode,
            'delivery_pincode' => (int) $deliveryPincode,
        ];

        $this->log('Checking Pincode Serviceability', $payload);

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/pincode-serviceability", $payload);

        $this->log('Pincode Serviceability Response', $response->json() ?? []);

        return $response->json() ?? [];
    }

    public function getRates(array $data): array
    {
        // Calculate total weight and dimensions from order/cart data
        $weight = 0;
        $totalValue = 0;
        $dimensions = [];

        $addressTo = Arr::get($data, 'address_to', []);
        $deliveryPincode = Arr::get($addressTo, 'zip_code') ?: Arr::get($addressTo, 'zip') ?: Arr::get($data, 'zip_code') ?: Arr::get($data, 'zip');

        if (!$deliveryPincode) {
            \Illuminate\Support\Facades\Log::info('Shipmozo getRates aborted: No delivery zipcode provided in payload.');
            return []; // Cannot calculate without delivery pin
        }

        $warehouseId = Arr::get($data, 'origin_warehouse_id');

        // Get pickup pincode from first vendor, or default store location
        $origin = Arr::get($data, 'origin') ?: EcommerceHelper::getOriginAddress();
        $pickupPincode = Arr::get($origin, 'zip_code', '');

        $items = Arr::get($data, 'items', []);
        $length = 0;
        $wide = 0;
        $height = 0;

        foreach ($items as $item) {
            $qty = $item['qty'] ?: 1;

            // Decimal inputs (e.g. 0.200) mean Kilograms in Botble. ShipMozo expects integer Grams.
            $rawWeight = (float)($item['weight'] ?: 0);
            $itemWeightInGrams = $rawWeight < 10 ? ($rawWeight * 1000) : $rawWeight;

            // If it evaluates to completely 0, default to 200g
            if ($itemWeightInGrams <= 0) {
                $itemWeightInGrams = 200;
            }

            $weight += $itemWeightInGrams * $qty;
            $totalValue += ($item['price'] ?: 0) * $qty;

            $dimensions[] = [
                'length' => floatval($item['length']) ?: 10,
                'width' => floatval($item['wide']) ?: 10,
                'height' => floatval($item['height']) ?: 10,
                'weight' => $itemWeightInGrams,
                'no_of_box' => 1,
            ];
            $length = max($length, floatval($item['length']) ?: 10);
            $wide = max($wide, floatval($item['wide']) ?: 10);
            $height = max($height, floatval($item['height']) ?: 10);
        }

        // Enforce global minimum weight safely ensuring the payload is never 0
        if ($weight <= 0) {
            $weight = 200;
        }

        $paymentMethod = Arr::get($data, 'payment_method');
        $isCod = $paymentMethod === \Botble\Payment\Enums\PaymentMethodEnum::COD;
        $paymentType = $isCod ? 'COD' : 'PREPAID';
        $codAmount = $isCod ? $this->formatAmount($totalValue) : "0.00";
        $amount = $this->formatAmount($totalValue);

        $payload = [
            'pickup_pincode' => (string) $pickupPincode,
            'delivery_pincode' => (string) $deliveryPincode,
            'weight' => (int) $weight,
            'dimensions' => [
                [
                    'length' => (float) max($length, 0),
                    'width' => (float) max($wide, 0),
                    'height' => (float) max($height, 0),
                    'weight' => (int) $weight,
                    'no_of_box' => 1
                ]
            ],
            'payment_type' => $paymentType,
            'shipment_type' => 'FORWARD',
            'order_amount' => $amount,
            'type_of_package' => 'SPS',
            'rov_type' => 'ROV_OWNER',
            'cod_amount' => $codAmount,
            'order_id' => '',
            'warehouse_id' => $warehouseId ? (string)$warehouseId : "",
        ];

        \Illuminate\Support\Facades\Log::info('ShipMozo getRates Payload: ', $payload);

        $response = \Illuminate\Support\Facades\Http::withHeaders($this->getHeaders())
            ->post($this->baseUrl . '/rate-calculator', $payload);

        $apiResult = $response->json();
        \Illuminate\Support\Facades\Log::info('ShipMozo getRates Response: ', (array) $apiResult);

        $responseData = $apiResult ?? [];

        $formattedRates = [];
        if (Arr::get($responseData, 'result') == 1 && !empty(Arr::get($responseData, 'data'))) {
            $apiRates = Arr::get($responseData, 'data');
            \Illuminate\Support\Facades\Log::info('ShipMozo Raw Courier Array Sample: ', (array) Arr::first($apiRates));

            // Handle Inflation
            $adjType = setting('shipping_shipmozo_rate_adjustment_type', 'none');
            $adjVal = (float) setting('shipping_shipmozo_rate_adjustment_value', 0);

            // The API response structure for rates (Assuming it returns an array of couriers)
            // If it returns a single recommended rate or array:
            if (isset($apiRates['charge'])) { // Single rate fallback mapping
                $apiRates = [$apiRates];
            }

            foreach ($apiRates as $rate) {
                $charge = (float) Arr::get($rate, 'charge', Arr::get($rate, 'shipping_charges', 0));

                if ($adjType === 'fixed') {
                    $charge += $adjVal;
                } elseif ($adjType === 'percent') {
                    $charge += ($charge * ($adjVal / 100));
                }

                $courierName = Arr::get($rate, 'name', 'ShipMozo Standard');
                $courierId = Arr::get($rate, 'id', 'shipmozo_standard');
                $courierKey = \Illuminate\Support\Str::slug((string) $courierId, '_');

                $formattedRates[$courierKey] = [
                    'id' => $courierKey,
                    'name' => $courierName,
                    'price' => $charge,
                    'estimated_delivery' => Arr::get($rate, 'estimated_delivery', '3-5 Days'),
                    'disabled' => false,
                    'error_message' => null,
                    'image' => Arr::get($rate, 'image')
                ];
            }
        }

        if (empty($formattedRates) && !empty($deliveryPincode)) {
            $formattedRates['no_service'] = [
                'id' => 'no_service',
                'name' => 'ShipMozo Delivery',
                'price' => 0,
                'estimated_delivery' => '',
                'disabled' => true,
                'error_message' => 'Sorry we do not deliver to this pincode, try another pincode.',
                'image' => null
            ];
        } else {
            // Check Botble's sort direction setting
            $sortDirection = setting('ecommerce_sort_shipping_options_direction', 'price_lower_to_higher');
            $sortDirection = setting('sort_shipping_options_direction', $sortDirection);

            uasort($formattedRates, function ($a, $b) use ($sortDirection) {
                if ($sortDirection === 'price_higher_to_lower') {
                    return $b['price'] <=> $a['price'];
                }
                // default price_lower_to_higher
                return $a['price'] <=> $b['price'];
            });
        }

        return ['shipment' => ['rates' => $formattedRates]];
    }

    public function pushOrder(Order $order): array
    {
        $address = $order->shippingAddress;

        $totalWeight = 0;
        $items = [];
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;

            // Dynamic Dimensions & Weight
            $itemLength = (float)($product ? ($product->length ?: 10) : 10);
            $itemWidth = (float)($product ? ($product->wide ?: 10) : 10);
            $itemHeight = (float)($product ? ($product->height ?: 10) : 10);
            $rawWeight = (float)($orderProduct->weight ?: ($product ? $product->weight : 0));

            // Weight Conversion logic (Botble kg -> ShipMozo g)
            $itemWeightInGrams = $rawWeight < 10 ? ($rawWeight * 1000) : $rawWeight;
            if ($itemWeightInGrams <= 0) {
                $itemWeightInGrams = 200;
            }

            $totalWeight += $itemWeightInGrams * $orderProduct->qty;
            $maxLength = max($maxLength, $itemLength);
            $maxWidth = max($maxWidth, $itemWidth);
            $maxHeight = max($maxHeight, $itemHeight);

            $categoryName = 'General';
            if ($product && $product->categories->first()) {
                $categoryName = $product->categories->first()->name;
            }

            // Dynamic Discount Calculation
            $actualPricePaid = (float)$orderProduct->price;
            $discount = 0;
            if ($product && $product->price > $product->sale_price && $product->sale_price > 0) {
                $discount = (float)($product->price - $product->sale_price);
            }

            // Consistency fix: if we send a discount, the unit_price MUST be the original price
            // so that unit_price - discount = actualPricePaid
            $unitPrice = $actualPricePaid + $discount;

            // Dynamic Tax Rate Extraction (Calculate from actual paid price to be safe)
            $taxRate = 0;
            if ($actualPricePaid > 0 && $orderProduct->tax_amount > 0) {
                $taxRate = round(($orderProduct->tax_amount / $actualPricePaid) * 100, 2);
            }

            // HSN Code - attempt to get from meta or barcode
            $hsnCode = '';
            try {
                $hsnCode = $product ? (get_meta($product, 'hsn_code') ?: ($product->barcode ?: '')) : '';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('ShipMozo: Failed to fetch hsn_code meta due to DB issue: ' . $e->getMessage());
                $hsnCode = $product ? ($product->barcode ?: '') : '';
            }

            $cleanCategory = preg_replace('/[^A-Za-z0-9 ]/', '', $categoryName);
            if (empty($cleanCategory)) {
                $cleanCategory = 'General';
            }

            $taxAmount = (float)($actualPricePaid * $taxRate / 100);

            $items[] = [
                'name' => $orderProduct->product_name,
                'sku_number' => $product ? $product->sku : 'N/A',
                'quantity' => (int) $orderProduct->qty,
                'unit_price' => (float) $unitPrice,
                //'tax_rate' => (float) $taxRate,
                //'tax_amount' => (float) $taxAmount,
                'discount' => (float) $discount,
                'product_category' => $cleanCategory,
                'hsn' => $hsnCode,
            ];
        }

        $origin = EcommerceHelper::getOriginAddress();

        $payment = $order->payment;
        $isCod = $payment && $payment->payment_channel->getValue() == \Botble\Payment\Enums\PaymentMethodEnum::COD;

        $phone = preg_replace('/[^0-9]/', '', (string)$address->phone);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $pickupLocation = 'Primary';
        if (is_plugin_active('marketplace') && $order->store_id) {
            $store = $order->store;
            if ($store) {
                // EXTREMELY AGGRESSIVE: Remove digits too, just in case
                $pickupLocation = 'Warehouse' . str_repeat('X', $store->id % 5) . $store->id;
                $pickupLocation = preg_replace('/[^A-Za-z]/', '', $pickupLocation);
                // Ensure synchronization uses the same clean name
                $this->syncWarehouse($store);
            }
        }

        $orderId = preg_replace('/[^A-Za-z0-9]/', '', $order->code ?? (string)$order->id);

        // Calculate amounts and clean items for the final payload
        $itemsTotal = 0;
        $cleanItems = [];
        foreach ($items as $item) {
            $uPrice = round((float)($item['unit_price'] ?? 0), 2);
            $uDiscount = round((float)($item['discount'] ?? 0), 2);
            $qty = (int)($item['quantity'] ?? 1);

            // Item total for our internal summation
            $itemsTotal += ($uPrice * $qty) - $uDiscount;

            $cleanItems[] = [
                'name' => preg_replace('/[^A-Za-z0-9 ]/', '', $item['name']),
                'sku_number' => (string)($item['sku_number'] ?? 'N/A'),
                'quantity' => $qty,
                'discount' => $uDiscount > 0 ? (string)number_format($uDiscount, 2, '.', '') : "",
                'hsn' => (string)($item['hsn'] ?? ''),
                'unit_price' => (float)$uPrice,
                'product_category' => (string)($item['product_category'] ?? 'General'),
            ];
        }

        $shippingCharged = round((float)$order->shipping_amount, 2);
        $taxAmount = round((float)$order->tax_amount, 2);
        $finalTotal = round($itemsTotal + $shippingCharged + $taxAmount, 2);
        $warehouseId = $order->store ? $order->store->warehouse_id : "";

        $payload = [
            'order_id' => $orderId,
            'order_date' => $order->created_at->format('Y-m-d'),
            'consignee_name' => $this->sanitize($address->name),
            'consignee_phone' => (int)$phone,
            'consignee_email' => $address->email,
            'consignee_address_line_one' => $this->sanitize($address->address),
            'consignee_address_line_two' => $this->sanitize($address->address_2 ?? ''),
            'consignee_pin_code' => (int) $address->zip_code,
            'consignee_city' => $this->sanitize($address->city_name),
            'consignee_state' => $this->sanitize($address->state_name),
            'product_detail' => $cleanItems,
            'payment_type' => $isCod ? 'COD' : 'PREPAID',
            'cod_amount' => $isCod ? $this->formatAmount($finalTotal) : "0.00",
            'shipping_charges' => $this->formatAmount($shippingCharged),
            'weight' => (int) max($totalWeight, 200),
            'length' => (int) max($maxLength, 10),
            'width' => (int) max($maxWidth, 10),
            'height' => (int) max($maxHeight, 10),
            'warehouse_id' => (string)$warehouseId,
            'gst_ewaybill_number' => "",
            'gstin_number' => "",
        ];

        $this->log('Push Order Request', $payload);

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/push-order", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Push Order Response', $responseData);

        return $responseData;
    }

    public function getOrderLabel(string $awbNumber): ?string
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/get-order-label/{$awbNumber}", [
                'type_of_label' => 'PDF'
            ]);

        if ($response->successful()) {
            return $response->json('data.url') ?? null;
        }

        return null;
    }

    public function cancelOrder(string $awbNumber): array
    {
        $payload = [
            'awb_number' => [$awbNumber],
        ];

        $this->log('Canceling Order', $payload);

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/cancel-order", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Cancel Order Response', $responseData);

        return $responseData;
    }

    public function pushReturnOrder(\Botble\Ecommerce\Models\OrderReturn $orderReturn): array
    {
        $order = $orderReturn->order;
        $address = $order->shippingAddress;

        $totalWeight = 0;
        $items = [];
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;

        foreach ($orderReturn->items as $returnItem) {
            $product = $returnItem->product;

            // Dynamic Dimensions & Weight
            $itemLength = (float)($product ? ($product->length ?: 10) : 10);
            $itemWidth = (float)($product ? ($product->wide ?: 10) : 10);
            $itemHeight = (float)($product ? ($product->height ?: 10) : 10);
            $rawWeight = (float)($product ? $product->weight : 0);

            // Weight Conversion
            $itemWeightInGrams = $rawWeight < 10 ? ($rawWeight * 1000) : $rawWeight;
            if ($itemWeightInGrams <= 0) {
                $itemWeightInGrams = 200;
            }

            $totalWeight += $itemWeightInGrams * $returnItem->qty;
            $maxLength = max($maxLength, $itemLength);
            $maxWidth = max($maxWidth, $itemWidth);
            $maxHeight = max($maxHeight, $itemHeight);

            $categoryName = 'General';
            if ($product && $product->categories->first()) {
                $categoryName = $product->categories->first()->name;
            }

            // Discount - hard to track in return but let's try to be consistent
            $discount = 0;
            $hsnCode = '';
            try {
                $hsnCode = $product ? (get_meta($product, 'hsn_code') ?: ($product->barcode ?: '')) : '';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('ShipMozo: Failed to fetch hsn_code meta in return due to DB issue: ' . $e->getMessage());
                $hsnCode = $product ? ($product->barcode ?: '') : '';
            }

            $items[] = [
                'name' => $this->sanitize($returnItem->product_name),
                'sku_number' => $product ? $product->sku : 'N/A',
                'quantity' => (int) $returnItem->qty,
                'unit_price' => $this->formatAmount($returnItem->price),
                'discount' => "0.00",
                'product_category' => $this->sanitize($categoryName),
                'hsn' => $hsnCode,
            ];
        }

        $origin = EcommerceHelper::getOriginAddress();

        $phone = preg_replace('/[^0-9]/', '', (string)$address->phone);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $payload = [
            'order_id' => 'RET_' . $order->id . '_' . time(),
            'order_amount' => $this->formatAmount($orderReturn->refund_amount ?? 0),
            'pickup_pincode' => (int) $address->zip_code,
            'pickup_address_line_one' => $this->sanitize($address->address),
            'pickup_address_line_two' => $this->sanitize($address->address_2 ?? ''),
            'pickup_name' => $this->sanitize($address->name),
            'pickup_phone' => $phone,
            'pickup_email' => $address->email,
            'pickup_city' => $this->sanitize($address->city_name),
            'pickup_state' => $this->sanitize($address->state_name),
            'delivery_pincode' => (int) Arr::get($origin, 'zip_code', 0),
            'product_detail' => $items,
            'weight' => (int) max($totalWeight, 200),
            'length' => (float) max($maxLength, 10),
            'width' => (float) max($maxWidth, 10),
            'height' => (float) max($maxHeight, 10),
        ];

        $this->log('Push Return Order Request', $payload);

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/push-return-order", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Push Return Order Response', $responseData);

        return $responseData;
    }

    public function getNdrAll(): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/get-ndr-all");

        return $response->json('data') ?? [];
    }

    public function ndrAction(string $awbNumber, string $action): array
    {
        // $action typically is 'reattempt' or 'rto' based on API docs.
        $payload = [
            'awb_number' => $awbNumber,
            'action' => $action
        ];

        $this->log('NDR Action Request', $payload);

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/ndr-action", $payload);

        $responseData = $response->json() ?? [];
        $this->log('NDR Action Response', $responseData);

        return $responseData;
    }

    public function getWarehouses(): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/get-warehouses");

        $responseData = $response->json() ?? [];
        $this->log('Get Warehouses Response', $responseData);

        return $responseData;
    }

    public function createWarehouse(array $data): array
    {
        $payload = [
            'name' => $this->sanitize(Arr::get($data, 'name')),
            'address_title' => $this->sanitize(Arr::get($data, 'address_title')),
            'contact_name' => $this->sanitize(Arr::get($data, 'contact_name')),
            'contact_email' => Arr::get($data, 'contact_email'),
            'contact_phone' => (int)preg_replace('/[^0-9]/', '', Arr::get($data, 'contact_phone')),
            'pincode' => (int)Arr::get($data, 'pincode'),
            'city' => $this->sanitize(Arr::get($data, 'city')),
            'state' => $this->sanitize(Arr::get($data, 'state')),
            'address' => $this->sanitize(Arr::get($data, 'address')),
            'phone' => (int)preg_replace('/[^0-9]/', '', Arr::get($data, 'phone')),
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/create-warehouse", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Create Warehouse Response', $responseData);

        return $responseData;
    }

    public function syncWarehouse($store): array
    {
        if ($store->warehouse_id) {
            return ['result' => 1, 'message' => 'Using linked warehouse_id', 'id' => $store->warehouse_id];
        }

        // AGGRESSIVE: No digits or chars in location name
        $locationName = preg_replace('/[^A-Za-z]/', '', 'Warehouse' . str_repeat('X', $store->id % 5) . $store->id);
        $payload = [
            'name' => $locationName,
            'address_title' => $locationName,
            'contact_name' => $this->sanitize($store->name, 'alpha'),
            'contact_email' => $store->email,
            'contact_phone' => (string)preg_replace('/[^0-9]/', '', $store->phone),
            'pincode' => (int)$store->zip_code,
            'city' => $this->sanitize($store->city_name ?? $store->city, 'alpha'),
            'state' => $this->sanitize($store->state_name ?? $store->state, 'alpha'),
            'address' => $this->sanitize($store->address),
            'phone' => (string)preg_replace('/[^0-9]/', '', $store->phone),
        ];

        $this->log('Syncing Warehouse to ShipMozo', $payload);

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/create-warehouse", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Sync Warehouse Response', $responseData);

        return $responseData;
    }

    public function trackOrder(string $awbNumber): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/track-order/{$awbNumber}");

        return $response->json('data') ?? [];
    }

    private function sanitize(?string $text, string $type = 'alphanumeric'): string
    {
        if (empty($text)) {
            return '';
        }

        if ($type === 'alpha') {
            return preg_replace('/[^A-Za-z ]/', '', $text);
        }

        // Default alphnumeric + space
        return preg_replace('/[^A-Za-z0-9 ]/', '', $text);
    }

    private function formatAmount($amount): string
    {
        return number_format((float)$amount, 2, '.', '');
    }
}
