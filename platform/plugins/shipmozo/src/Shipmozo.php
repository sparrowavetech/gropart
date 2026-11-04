<?php

namespace SparroWave\Shipmozo;

use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentMethodEnum;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SparroWave\Shipmozo\Supports\PackageDimensionsCalculator;

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
        if (! $shipment->order || $shipment->tracking_id) {
            return false;
        }

        return $this->isShipmozoOrder($shipment->order);
    }

    public function isShipmozoOrder(Order $order): bool
    {
        if (is_plugin_active('marketplace') && $order->store_id) {
            $store = $order->store ?: (class_exists(\Botble\Marketplace\Models\Store::class) ? \Botble\Marketplace\Models\Store::find($order->store_id) : null);
            if ($store && (bool) $store->vendor_managed_shipping) {
                return false;
            }
        }

        $method = $order->shipping_method;
        if (is_object($method) && method_exists($method, 'getValue')) {
            $method = $method->getValue();
        } elseif (is_object($method) && isset($method->value)) {
            $method = $method->value;
        } elseif (! is_object($method)) {
            $method = (string) $method;
        } else {
            $method = '';
        }

        if ($method === SHIPMOZO_SHIPPING_METHOD_NAME) {
            return true;
        }

        if ($method === ShippingMethodEnum::DEFAULT && $order->shipping_option === 'free_shipping') {
            if (! setting('shipping_shipmozo_status')) {
                return false;
            }

            if (setting('shipping_shipmozo_scope', 'all') === 'individual') {
                $hasEnabled = false;
                foreach ($order->products as $orderProduct) {
                    $prod = $orderProduct->product;
                    if ($prod && (is_callable([$prod, 'isShipmozoShippingEnabled']) ? $prod->isShipmozoShippingEnabled() : (bool) $prod->enable_shipmozo_shipping)) {
                        $hasEnabled = true;
                        break;
                    }
                }

                return $hasEnabled;
            }

            return true;
        }

        return false;
    }

    public function getRoutePrefixByFactor(): string
    {
        return (is_in_admin(true) || ! is_plugin_active('marketplace')) ? 'ecommerce.shipments.' : 'marketplace.vendor.orders.';
    }

    public function convertWeightToGrams(float $rawWeight): int
    {
        return PackageDimensionsCalculator::normalizeWeightInGrams($rawWeight);
    }

    protected function getHeaders(): array
    {
        return [
            'public-key' => (string) $this->publicKey,
            'private-key' => (string) $this->privateKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function httpClient(bool $retry = false): PendingRequest
    {
        if (! $this->publicKey || ! $this->privateKey) {
            throw new RuntimeException('ShipMozo API credentials are not configured.');
        }

        $client = Http::withHeaders($this->getHeaders())
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(20);

        return $retry ? $client->retry(2, 250, null, false) : $client;
    }

    public function log(string $message, array $context = []): void
    {
        if ($this->logging) {
            Log::channel('shipmozo')->info($message, $this->redactLogContext($context));
        }
    }

    public function logError(string $message, array $context = []): void
    {
        if ($this->logging) {
            Log::channel('shipmozo')->error($message, $this->redactLogContext($context));
        }
    }

    public function checkPincodeServiceability(string $pickupPincode, string $deliveryPincode): array
    {
        if (! self::validPincode($pickupPincode) || ! self::validPincode($deliveryPincode)) {
            return self::invalidPincodeResponse();
        }

        $payload = [
            'pickup_pincode' => (int) $pickupPincode,
            'delivery_pincode' => (int) $deliveryPincode,
        ];

        $this->log('Checking Pincode Serviceability', $payload);

        $response = $this->httpClient(true)
            ->post("{$this->baseUrl}/pincode-serviceability", $payload);

        $this->log('Pincode Serviceability Response', $response->json() ?? []);

        return $response->json() ?? [];
    }

    public function getRates(array $data): array
    {
        // Calculate total weight and dimensions from order/cart data
        $weight = 0;
        $totalValue = 0;
        $addressTo = Arr::get($data, 'address_to', []);
        $deliveryPincode = Arr::get($addressTo, 'zip_code') ?: Arr::get($addressTo, 'zip') ?: Arr::get($data, 'zip_code') ?: Arr::get($data, 'zip');

        if (! preg_match('/^[1-9][0-9]{5}$/', (string) $deliveryPincode)) {
            return ['shipment' => ['rates' => $this->unavailableRates('destination')]];
        }

        // Get pickup pincode from first vendor, or default store location
        $origin = Arr::get($data, 'origin') ?: EcommerceHelper::getOriginAddress();
        $pickupPincode = Arr::get($origin, 'zip_code') ?: Arr::get($origin, 'zip') ?: Arr::get($origin, 'postal_code', '');
        if (! preg_match('/^[1-9][0-9]{5}$/', (string) $pickupPincode)) {
            return ['shipment' => ['rates' => $this->unavailableRates('pickup')]];
        }

        $items = Arr::get($data, 'items', []);
        $billableItemCount = 0;
        $billableItems = [];

        foreach ($items as $productId => $item) {
            $qty = max((int) Arr::get($item, 'qty', 1), 1);

            // Check if this specific item has free delivery enabled
            $isFreeShipping = (bool) apply_filters('ecommerce_product_has_free_shipping', false, $item + ['id' => $productId]);

            if ($isFreeShipping && empty($data['shipmozo_serviceability_only'])) {
                // Free shipping product -> skip adding weight and dimensions to ShipMozo billable payload
                continue;
            }

            $billableItemCount++;
            $totalValue += (float) Arr::get($item, 'price', 0) * $qty;
            $billableItems[] = $item;
        }

        // If all items in this package are free shipping, bypass ShipMozo rate calculation (Free Delivery applies)
        if (! empty($items) && $billableItemCount === 0) {
            return [];
        }

        if (empty($billableItems) && $weight > 0) {
            $billableItems[] = ['weight' => $weight, 'qty' => 1];
        }

        $packageCalc = PackageDimensionsCalculator::calculate($billableItems);
        $weight = $packageCalc['chargeable_weight_grams'];
        $length = $packageCalc['length'];
        $wide = $packageCalc['width'];
        $height = $packageCalc['height'];

        $paymentMethod = Arr::get($data, 'payment_method');
        $isCod = $paymentMethod == PaymentMethodEnum::COD;
        $paymentType = $isCod ? 'COD' : 'PREPAID';
        $codAmount = $isCod ? $this->formatAmount($totalValue) : '';
        $amount = round($totalValue, 2);

        $payload = [
            'pickup_pincode' => (int) $pickupPincode,
            'delivery_pincode' => (int) $deliveryPincode,
            'weight' => (int) $weight,
            'dimensions' => [
                [
                    'no_of_box' => '1',
                    'length' => (string) max($length, 1),
                    'width' => (string) max($wide, 1),
                    'height' => (string) max($height, 1),
                ],
            ],
            'payment_type' => $paymentType,
            'shipment_type' => 'FORWARD',
            'order_amount' => $amount,
            'type_of_package' => 'SPS',
            'rov_type' => 'ROV_OWNER',
            'cod_amount' => $codAmount,
            'order_id' => '',
        ];

        $this->log('Rate calculator request', $payload);

        $response = $this->httpClient(true)
            ->post($this->baseUrl.'/rate-calculator', $payload);

        $apiResult = $response->json();
        $this->log('Rate calculator response', (array) $apiResult);

        $responseData = is_array($apiResult) ? $apiResult : [];

        $formattedRates = [];
        if (Arr::get($responseData, 'result') == 1 && ! empty(Arr::get($responseData, 'data'))) {
            $apiRates = Arr::get(
                $responseData,
                'data.rates',
                Arr::get($responseData, 'data.courier_rates', Arr::get($responseData, 'data'))
            );
            // Handle Inflation
            $adjType = setting('shipping_shipmozo_rate_adjustment_type', 'none');
            $adjVal = (float) setting('shipping_shipmozo_rate_adjustment_value', 0);

            if (! is_array($apiRates)) {
                $apiRates = [];
            } elseif (Arr::hasAny($apiRates, ['courier_id', 'id', 'charge', 'shipping_charges', 'total_charge'])) {
                $apiRates = [$apiRates];
            }

            foreach ($apiRates as $rate) {
                if (! is_array($rate)) {
                    continue;
                }

                $rawShippingCharges = (float) Arr::get($rate, 'shipping_charges', 0);
                $gst = (float) Arr::get($rate, 'gst', 0);
                $overheadCharges = (float) Arr::get($rate, 'overhead_charges', 0);
                $actualTotalCharges = (float) Arr::get(
                    $rate,
                    'total_charges',
                    Arr::get(
                        $rate,
                        'total_charge',
                        $rawShippingCharges > 0 ? ($rawShippingCharges + $gst + $overheadCharges) : 0
                    )
                );

                $charge = (float) Arr::get(
                    $rate,
                    'total_charge',
                    Arr::get(
                        $rate,
                        'charge',
                        Arr::get(
                            $rate,
                            'shipping_charges',
                            Arr::get($rate, 'total_charges', Arr::get($rate, 'rate', 0))
                        )
                    )
                );

                if ($charge <= 0 && $actualTotalCharges <= 0) {
                    continue;
                }

                if ($charge <= 0) {
                    $charge = $actualTotalCharges;
                }

                $uninflatedCharge = $charge;

                if ($adjType === 'fixed') {
                    $charge += $adjVal;
                } elseif ($adjType === 'percent') {
                    $charge += ($charge * ($adjVal / 100));
                }

                $courierName = Arr::get($rate, 'courier_name', Arr::get($rate, 'name', 'ShipMozo Standard'));
                $courierId = Arr::get($rate, 'courier_id', Arr::get($rate, 'id'));

                if (! is_numeric($courierId)) {
                    continue;
                }

                $automaticPickup = strtoupper((string) Arr::get($rate, 'pickups_automatically_scheduled'));
                $pickupSuffix = match ($automaticPickup) {
                    'YES' => '_auto',
                    'NO' => '_manual',
                    default => '',
                };
                $courierKey = 'shipmozo_'.(int) $courierId.$pickupSuffix;

                $formattedRates[$courierKey] = [
                    'id' => $courierKey,
                    'name' => $courierName,
                    'price' => $charge,
                    'actual_charge' => $actualTotalCharges > 0 ? $actualTotalCharges : $uninflatedCharge,
                    'raw_shipping_charges' => $rawShippingCharges > 0 ? $rawShippingCharges : $uninflatedCharge,
                    'gst' => $gst,
                    'overhead_charges' => $overheadCharges,
                    'customer_shipmozo_price' => $charge,
                    'courier_id' => (int) $courierId,
                    'pickups_automatically_scheduled' => Arr::get($rate, 'pickups_automatically_scheduled'),
                    'estimated_delivery' => Arr::get($rate, 'estimated_delivery') ?: Arr::get($rate, 'etd', ''),
                    'package_weight_grams' => (int) $weight,
                    'disabled' => false,
                    'error_message' => null,
                    'image' => Arr::get($rate, 'courier_logo', Arr::get($rate, 'image')),
                ];
            }
        }

        if (empty($formattedRates) && ! empty($deliveryPincode)) {
            $message = strtolower((string) Arr::get($responseData, 'message', ''));
            $reason = match (true) {
                str_contains($message, 'pickup') => 'pickup',
                str_contains($message, 'delivery pincode') && str_contains($message, 'invalid') => 'destination',
                Arr::get($responseData, 'result') == 1,
                str_contains($message, 'serviceab'), str_contains($message, 'not service') => 'unavailable',
                default => 'api_error',
            };
            $formattedRates = $this->unavailableRates($reason);
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

    public function unavailableRates(string $reason): array
    {
        $message = match ($reason) {
            'destination' => __('Enter a valid six-digit delivery PIN to check shipping.'),
            'pickup' => __('Shipping is unavailable from this seller location. Please contact support.'),
            'unavailable' => __('No courier is available for this pickup and delivery route. Try another delivery PIN or contact support.'),
            default => __('Shipping rates could not be checked. Please retry in a moment.'),
        };

        return ['no_service' => [
            'id' => 'no_service', 'name' => 'ShipMozo Delivery', 'price' => 0,
            'disabled' => true, 'error_message' => $message,
            'shipmozo_status' => $reason, 'estimated_delivery' => '', 'image' => null,
        ]];
    }

    public function pushOrder(Order $order): array
    {
        $address = $order->shippingAddress;
        if (! $address || ! self::validPincode($address->zip_code)) {
            return self::invalidPincodeResponse();
        }

        $items = [];
        $isIndividualScope = setting('shipping_shipmozo_scope', 'all') === 'individual';
        $packageCalc = PackageDimensionsCalculator::calculateForOrder($order, $isIndividualScope);

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;

            if ($isIndividualScope) {
                if (! $product) {
                    continue;
                }

                $isItemEligible = is_callable([$product, 'isShipmozoShippingEnabled'])
                    ? $product->isShipmozoShippingEnabled()
                    : (bool) $product->enable_shipmozo_shipping;

                if (! $isItemEligible) {
                    continue;
                }
            }

            $categoryName = 'General';
            if ($product && $product->categories->first()) {
                $categoryName = $product->categories->first()->name;
            }

            // Dynamic Discount Calculation
            $actualPricePaid = (float) $orderProduct->price;
            $discount = 0;
            if ($product && $product->price > $product->sale_price && $product->sale_price > 0) {
                $discount = (float) ($product->price - $product->sale_price);
            }

            // Consistency fix: if we send a discount, the unit_price MUST be the original price
            // so that unit_price - discount = actualPricePaid
            $unitPrice = $actualPricePaid + $discount;

            // HSN Code - attempt to get from meta or barcode
            $hsnCode = '';
            try {
                $hsnCode = $product ? (get_meta($product, 'hsn_code') ?: ($product->barcode ?: '')) : '';
            } catch (\Exception $e) {
                $this->logError('Unable to read product HSN metadata', ['message' => $e->getMessage()]);
                $hsnCode = $product ? ($product->barcode ?: '') : '';
            }

            $cleanCategory = $this->sanitize($categoryName);
            if (empty($cleanCategory)) {
                $cleanCategory = 'General';
            }

            $items[] = [
                'name' => $orderProduct->product_name,
                'sku_number' => $product ? $product->sku : 'N/A',
                'quantity' => (int) $orderProduct->qty,
                'unit_price' => (float) $unitPrice,
                // 'tax_rate' => (float) $taxRate,
                // 'tax_amount' => (float) $taxAmount,
                'discount' => (float) $discount,
                'product_category' => $cleanCategory,
                'hsn' => $hsnCode,
            ];
        }

        if (empty($items)) {
            return [
                'result' => 0,
                'message' => 'No ShipMozo-eligible items found in this order for shipment booking.',
            ];
        }

        $origin = EcommerceHelper::getOriginAddress();

        $collectionAmount = \SparroWave\Shipmozo\Supports\CollectionAmount::forOrder($order);
        $isCod = $collectionAmount > 0;

        $phone = preg_replace('/[^0-9]/', '', (string) $address->phone);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $warehouseId = $this->resolveWarehouseId($order);

        $orderId = $this->getOrderId($order);

        // Calculate amounts and clean items for the final payload
        $cleanItems = [];
        foreach ($items as $item) {
            $uPrice = round((float) ($item['unit_price'] ?? 0), 2);
            $uDiscount = round((float) ($item['discount'] ?? 0), 2);
            $qty = (int) ($item['quantity'] ?? 1);

            $cleanItems[] = [
                'name' => $this->sanitize($item['name']),
                'sku_number' => (string) ($item['sku_number'] ?? 'N/A'),
                'quantity' => $qty,
                'discount' => $uDiscount > 0 ? (string) number_format($uDiscount, 2, '.', '') : '',
                'hsn' => (string) ($item['hsn'] ?? ''),
                'unit_price' => (float) $uPrice,
                'product_category' => (string) ($item['product_category'] ?? 'General'),
            ];
        }

        $finalTotal = round((float) $order->amount, 2);
        $payload = [
            'order_id' => $orderId,
            'order_date' => $order->created_at->format('Y-m-d'),
            'consignee_name' => $this->sanitize($address->name),
            'consignee_phone' => (int) $phone,
            'consignee_email' => $address->email,
            'consignee_address_line_one' => $this->sanitize($address->address),
            'consignee_address_line_two' => $this->sanitize($address->address_2 ?? ''),
            'consignee_pin_code' => (int) $address->zip_code,
            'consignee_city' => $this->sanitize($address->city_name),
            'consignee_state' => $this->sanitize($address->state_name),
            'product_detail' => $cleanItems,
            'payment_type' => $isCod ? 'COD' : 'PREPAID',
            'cod_amount' => $isCod ? $this->formatAmount($collectionAmount) : '',
            'weight' => (int) $packageCalc['chargeable_weight_grams'],
            'length' => (int) $packageCalc['length'],
            'width' => (int) $packageCalc['width'],
            'height' => (int) $packageCalc['height'],
            'warehouse_id' => (string) $warehouseId,
            'gst_ewaybill_number' => '',
            'gstin_number' => '',
        ];

        $this->log('Push Order Request', $payload);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/push-order", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Push Order Response', $responseData);

        return $responseData;
    }

    public function getOrderId(Order $order): string
    {
        return trim((string) ($order->code ?: $order->id));
    }

    public function createShipment(Order $order, ?string $existingOrderId = null): array
    {
        $push = [];

        if ($existingOrderId) {
            $orderId = $existingOrderId;
        } else {
            $push = $this->pushOrder($order);

            if (Arr::get($push, 'result') != 1) {
                $error = (string) (Arr::get($push, 'data.error') ?: Arr::get($push, 'message'));
                if (! preg_match('/already|exist|duplicate/i', $error)) {
                    return $push;
                }
            }

            $orderId = (string) (Arr::get($push, 'data.order_id')
                ?: Arr::get($push, 'data.reference_id')
                ?: $this->getOrderId($order));
        }

        $workflow = $push ? ['push_order' => $push] : ['existing_order_id' => $orderId];

        $courierId = $this->courierIdFromShippingOption($order->shipping_option);
        if ($courierId === null) {
            $autoAssign = $this->autoAssignOrder($orderId);
            $workflow['auto_assign'] = $autoAssign;

            if (Arr::get($autoAssign, 'result') != 1) {
                return $this->workflowFailure(
                    Arr::get($autoAssign, 'data.error', Arr::get($autoAssign, 'message', 'ShipMozo auto assignment failed.')),
                    $workflow
                );
            }

            $detail = $this->getOrderDetail($orderId);
            $workflow['order_detail'] = $detail;
            $awb = $this->extractAwb($autoAssign) ?: $this->extractAwb($detail);

            return $awb
                ? $this->workflowSuccess($awb, $workflow)
                : $this->workflowFailure('ShipMozo assigned a courier but did not return an AWB.', $workflow);
        }

        $assignment = $this->assignCourier($orderId, $courierId);
        $workflow['assign_courier'] = $assignment;

        if (Arr::get($assignment, 'result') != 1) {
            return $this->workflowFailure(
                Arr::get($assignment, 'data.error', Arr::get($assignment, 'message', 'ShipMozo courier assignment failed.')),
                $workflow
            );
        }

        $detail = $this->getOrderDetail($orderId);
        $workflow['order_detail'] = $detail;
        $awb = $this->extractAwb($assignment) ?: $this->extractAwb($detail);

        if ($this->pickupModeFromShippingOption($order->shipping_option) !== 'auto') {
            $pickup = $this->schedulePickup($orderId);
            $workflow['schedule_pickup'] = $pickup;
            $awb = $awb ?: $this->extractAwb($pickup);
        }

        return $awb
            ? $this->workflowSuccess($awb, $workflow)
            : $this->workflowFailure('ShipMozo created the order but did not return an AWB.', $workflow);
    }

    public function assignCourier(string $orderId, int $courierId): array
    {
        return $this->httpClient()
            ->post("{$this->baseUrl}/assign-courier", [
                'order_id' => $orderId,
                'courier_id' => $courierId,
            ])->json() ?? [];
    }

    public function schedulePickup(string $orderId): array
    {
        return $this->httpClient()
            ->post("{$this->baseUrl}/schedule-pickup", ['order_id' => $orderId])
            ->json() ?? [];
    }

    public function autoAssignOrder(string $orderId): array
    {
        return $this->httpClient()
            ->post("{$this->baseUrl}/auto-assign-order", ['order_id' => $orderId])
            ->json() ?? [];
    }

    public function getOrderDetail(string $orderId): array
    {
        return $this->httpClient(true)
            ->get("{$this->baseUrl}/get-order-detail/".rawurlencode($orderId))
            ->json() ?? [];
    }

    public function getOrderLabel(string $awbNumber): ?array
    {
        $response = $this->httpClient(true)
            ->get("{$this->baseUrl}/get-order-label/".rawurlencode($awbNumber));

        $label = $response->json('data.0.label');
        if (! $response->successful() || ! is_string($label)) {
            return null;
        }

        if (! preg_match('/\Adata:(image\/(?:png|jpeg));base64,(.+)\z/s', $label, $matches)) {
            return null;
        }

        $contents = base64_decode($matches[2], true);

        return $contents === false ? null : ['mime_type' => $matches[1], 'contents' => $contents];
    }

    public function getOrderLabelUrl(string $awbNumber): string
    {
        return route('shipmozo.public.label', [
            'awbNumber' => $awbNumber,
            'signature' => $this->labelSignature($awbNumber),
        ]);
    }

    public function labelSignature(string $awbNumber): string
    {
        return hash_hmac('sha256', $awbNumber, (string) config('app.key'));
    }

    public function cancelOrder(string $orderId, string $awbNumber): array
    {
        $payload = [
            'order_id' => $orderId,
            'awb_number' => $awbNumber,
        ];

        $this->log('Canceling Order', $payload);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/cancel-order", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Cancel Order Response', $responseData);

        return $responseData;
    }

    public function pushReturnOrder(OrderReturn $orderReturn): array
    {
        $order = $orderReturn->order;
        $address = $order->shippingAddress;
        if (! $address || ! self::validPincode($address->zip_code)) {
            return self::invalidPincodeResponse();
        }

        $items = [];
        $returnCalc = PackageDimensionsCalculator::calculateForReturn($orderReturn);

        foreach ($orderReturn->items as $returnItem) {
            $product = $returnItem->product;

            $categoryName = 'General';
            if ($product && $product->categories->first()) {
                $categoryName = $product->categories->first()->name;
            }

            $hsnCode = '';
            try {
                $hsnCode = $product ? (get_meta($product, 'hsn_code') ?: ($product->barcode ?: '')) : '';
            } catch (\Exception $e) {
                $this->logError('Unable to read return product HSN metadata', ['message' => $e->getMessage()]);
                $hsnCode = $product ? ($product->barcode ?: '') : '';
            }

            $items[] = [
                'name' => $this->sanitize($returnItem->product_name),
                'sku_number' => $product ? $product->sku : 'N/A',
                'quantity' => (int) $returnItem->qty,
                'unit_price' => round((float) $returnItem->price, 2),
                'discount' => '',
                'product_category' => $this->sanitize($categoryName),
                'hsn' => $hsnCode,
            ];
        }

        $phone = preg_replace('/[^0-9]/', '', (string) $address->phone);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $payload = [
            'order_id' => (string) ($orderReturn->code ?: 'RET'.$orderReturn->id),
            'order_date' => $orderReturn->created_at->format('Y-m-d'),
            'pickup_name' => $this->sanitize($address->name),
            'pickup_phone' => (int) $phone,
            'pickup_email' => $address->email,
            'pickup_address_line_one' => $this->sanitize($address->address),
            'pickup_address_line_two' => $this->sanitize($address->address_2 ?? ''),
            'pickup_pin_code' => (int) $address->zip_code,
            'pickup_city' => $this->sanitize($address->city_name),
            'pickup_state' => $this->sanitize($address->state_name),
            'product_detail' => $items,
            'payment_type' => 'PREPAID',
            'weight' => round($returnCalc['chargeable_weight_grams'] / 1000, 3),
            'length' => (float) $returnCalc['length'],
            'width' => (float) $returnCalc['width'],
            'height' => (float) $returnCalc['height'],
            'warehouse_id' => $this->resolveWarehouseId($order),
            'return_reason_id' => $this->returnReasonId($orderReturn),
            'customer_request' => 'REFUND',
            'reason_comment' => (string) ($orderReturn->reason?->label() ?? ''),
        ];

        $this->log('Push Return Order Request', $payload);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/push-return-order", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Push Return Order Response', $responseData);

        return $responseData;
    }

    public function getNdrAll(): array
    {
        $response = $this->httpClient(true)
            ->get("{$this->baseUrl}/get-ndr-all");

        return $response->json('data') ?? [];
    }

    public function ndrAction(string $awbNumber, string $action): array
    {
        $payload = [
            'awb_number' => $awbNumber,
            'action' => $action,
        ];

        $this->log('NDR Action Request', $payload);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/ndr-action", $payload);

        $responseData = $response->json() ?? [];
        $this->log('NDR Action Response', $responseData);

        return $responseData;
    }

    public function getWarehouses(): array
    {
        $response = $this->httpClient(true)
            ->get("{$this->baseUrl}/get-warehouses");

        $responseData = $response->json() ?? [];
        $this->log('Get Warehouses Response', $responseData);

        return $responseData;
    }

    public function createWarehouse(array $data): array
    {
        if (! self::validPincode(Arr::get($data, 'pincode'))) {
            return self::invalidPincodeResponse();
        }

        $payload = [
            'address_title' => $this->sanitize(Arr::get($data, 'address_title')),
            'name' => $this->sanitize(Arr::get($data, 'contact_name', Arr::get($data, 'name'))),
            'phone' => (int) preg_replace('/[^0-9]/', '', Arr::get($data, 'phone')),
            'alternate_phone' => (int) preg_replace('/[^0-9]/', '', Arr::get($data, 'alternate_phone', '')),
            'email' => Arr::get($data, 'contact_email'),
            'address_line_one' => $this->sanitize(Arr::get($data, 'address')),
            'address_line_two' => $this->sanitize(Arr::get($data, 'address_line_two', '')),
            'pin_code' => (int) Arr::get($data, 'pincode'),
        ];

        $response = $this->httpClient()
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

        if (! self::validPincode($store->zip_code)) {
            return self::invalidPincodeResponse();
        }

        $locationName = $this->warehouseName($store->getKey());
        $payload = [
            'address_title' => $locationName,
            'name' => $this->sanitize($store->name, 'alpha'),
            'phone' => (int) preg_replace('/[^0-9]/', '', $store->phone),
            'alternate_phone' => 0,
            'email' => $store->email,
            'address_line_one' => $this->sanitize($store->address),
            'address_line_two' => '',
            'pin_code' => (int) $store->zip_code,
        ];

        $this->log('Syncing Warehouse to ShipMozo', $payload);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/create-warehouse", $payload);

        $responseData = $response->json() ?? [];
        $this->log('Sync Warehouse Response', $responseData);

        return $responseData;
    }

    private static function validPincode(mixed $pincode): bool
    {
        return is_scalar($pincode) && preg_match('/^[1-9][0-9]{5}$/', (string) $pincode) === 1;
    }

    private static function invalidPincodeResponse(): array
    {
        return ['result' => 0, 'data' => ['error' => trans('plugins/shipmozo::shipmozo.api_invalid_pincode')]];
    }

    public function trackOrder(string $awbNumber): array
    {
        $response = $this->httpClient(true)
            ->get("{$this->baseUrl}/track-order", ['awb_number' => $awbNumber]);

        return $response->json('data') ?? [];
    }

    private function resolveWarehouseId(Order $order): string
    {
        if (is_plugin_active('marketplace') && $order->store_id && $order->store) {
            $store = $order->store;
            $warehouse = $this->syncWarehouse($store);
            $warehouseId = (string) ($store->warehouse_id
                ?: Arr::get($warehouse, 'data.warehouse_id', Arr::get($warehouse, 'id', '')));

            if ($warehouseId !== '' && ! $store->warehouse_id) {
                $store->warehouse_id = $warehouseId;
                $store->saveQuietly();
            }

            if ($warehouseId !== '') {
                return $warehouseId;
            }
        }

        $warehouses = (array) Arr::get($this->getWarehouses(), 'data', []);
        $warehouse = Arr::first($warehouses, fn (array $item): bool => strtoupper((string) Arr::get($item, 'default')) === 'YES'
            && strtoupper((string) Arr::get($item, 'status', 'ACTIVE')) === 'ACTIVE'
        ) ?: Arr::first($warehouses, fn (array $item): bool => strtoupper((string) Arr::get($item, 'status', 'ACTIVE')) === 'ACTIVE'
        );

        $warehouseId = (string) Arr::get((array) $warehouse, 'id', '');
        if ($warehouseId !== '') {
            return $warehouseId;
        }

        $origin = EcommerceHelper::getOriginAddress();
        $created = $this->createWarehouse([
            'address_title' => 'BotbleOrigin',
            'name' => Arr::get($origin, 'name', 'Botble Store'),
            'contact_name' => Arr::get($origin, 'name', 'Botble Store'),
            'contact_email' => Arr::get($origin, 'email'),
            'phone' => Arr::get($origin, 'phone'),
            'address' => Arr::get($origin, 'address'),
            'address_line_two' => Arr::get($origin, 'address_2'),
            'pincode' => Arr::get($origin, 'zip_code'),
        ]);
        $warehouseId = (string) Arr::get($created, 'data.warehouse_id', '');

        if ($warehouseId === '') {
            throw new RuntimeException('ShipMozo requires an active warehouse before an order can be pushed.');
        }

        return $warehouseId;
    }

    private function courierIdFromShippingOption(mixed $shippingOption): ?int
    {
        $shippingOption = (string) $shippingOption;

        if (preg_match('/\Ashipmozo_(\d+)(?:_(?:auto|manual))?\z/', $shippingOption, $matches)) {
            return (int) $matches[1];
        }

        return ctype_digit($shippingOption) ? (int) $shippingOption : null;
    }

    private function pickupModeFromShippingOption(mixed $shippingOption): ?string
    {
        return preg_match('/_(auto|manual)\z/', (string) $shippingOption, $matches)
            ? $matches[1]
            : null;
    }

    private function extractAwb(array $response): ?string
    {
        $awb = Arr::get($response, 'awb_number')
            ?: Arr::get($response, 'data.awb_number')
            ?: Arr::get($response, 'data.0.awb_number')
            ?: Arr::get($response, 'data.0.zone.awb_number');

        return is_scalar($awb) && (string) $awb !== '' ? (string) $awb : null;
    }

    private function workflowSuccess(string $awb, array $workflow): array
    {
        return [
            'result' => '1',
            'message' => 'Success',
            'data' => ['awb_number' => $awb],
            'workflow' => $workflow,
        ];
    }

    private function workflowFailure(string $message, array $workflow): array
    {
        return [
            'result' => '0',
            'message' => $message,
            'data' => ['error' => $message],
            'workflow' => $workflow,
        ];
    }

    private function returnReasonId(OrderReturn $orderReturn): int
    {
        $reason = $orderReturn->reason?->getValue()
            ?: $orderReturn->items->first()?->reason?->getValue()
            ?: 'other';

        return match ($reason) {
            'arrived_late' => 1,
            'no_longer_want' => 8,
            'damaged', 'defective' => 9,
            'incorrect_item' => 10,
            'not_as_described' => 12,
            default => 14,
        };
    }

    private function sanitize(?string $text, string $type = 'alphanumeric'): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', trim($text)) ?? '';
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        if ($type === 'alpha') {
            return preg_replace('/[^\pL ]/u', '', $text) ?? '';
        }

        return $text;
    }

    private function formatAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function redactLogContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (preg_match('/key|token|secret|email|phone|address|consignee|pickup_name/i', (string) $key)) {
                $context[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $context[$key] = $this->redactLogContext($value);
            }
        }

        return $context;
    }

    private function warehouseName(int|string $storeId): string
    {
        $number = max((int) $storeId, 1);
        $suffix = '';

        while ($number > 0) {
            $number--;
            $suffix = chr(65 + ($number % 26)).$suffix;
            $number = intdiv($number, 26);
        }

        return 'Warehouse'.$suffix;
    }
}
